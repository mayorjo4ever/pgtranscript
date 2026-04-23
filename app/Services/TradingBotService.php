<?php

namespace App\Services;

use App\Models\Trade;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class TradingBotService
{
    protected BitgetService $bitget;

    public function __construct(BitgetService $bitget)
    {
        $this->bitget = $bitget;
    }
    
   public function handle(): void
    {
        Log::info("🤖 Bot running");
        if (!cache()->add('bot-running', true, 55)) {
            Log::warning('Bot already running, skipping...');
            return;
        }

        if (!Setting::isBotEnabled()) {
            Log::info('Bot disabled');
            return;
        }
        // Setting::where('key', 'bot_target_profit')->update(['value' => 4]);
        // ===============================
        // 📊 MARKET DATA
        // ===============================
        $prices = $this->getPrices();
        $currentPrice = $this->bitget->getPrice();
        // 🎯 Target profit (configurable)
        $targetPercent = (float) (
            Setting::where('key', 'target_profit_percent')->value('value') ?? 2
        );

        if (empty($prices) || !$currentPrice) {
            Log::warning('Invalid market data');
            return;
        }

        // ===============================
        // 🧠 INDICATORS
        // ===============================
        $rsi  = $this->calculateRSI($prices);
        $ma10 = $this->movingAverage($prices, 10);
        $ma50 = $this->movingAverage($prices, 50);

        $trendUp = $ma10 > $ma50;

        // ===============================
        // 💰 BALANCE
        // ===============================
        $balances = $this->bitget->getBalances();
        $btcBalance = $this->getBtcBalance();    

        $usdt = collect($balances['data'] ?? [])
            ->firstWhere('coin', 'USDT')['available'] ?? 0;

        Log::info("BTC Balance: {$btcBalance}");
        Log::info("USDT Balance: {$usdt}");
        
        // ==========================================================
        // 🔴 MODE 1: WE HAVE BTC → ONLY SELL LOGIC
        // ==========================================================
        if ($btcBalance > 0.00001) {

            Log::info('📊 SELL MODE ACTIVE');

            $lastTrade = Trade::where('side', 'buy')->latest()->first();

            if (!$lastTrade) {
                Log::warning('No BUY trade found');
                return;
            }

            $entry = (float) $lastTrade->price;

            // ✅ ALWAYS USE SAFE AMOUNT (avoid insufficient balance)
            $safeAmount = $btcBalance * 0.98; // leave 2% for fees

            // ✅ Apply exchange precision (BTC = 6 decimals safe)
            $safeAmount = floor($safeAmount * 1000000) / 1000000;

            $profitPercent = (($currentPrice - $entry) / $entry) * 100;

            Log::info('Position status', [
                'entry' => $entry,
                'current' => $currentPrice,
                'profit%' => round($profitPercent, 3),
                'rsi' => $rsi,
                'btcBalance' => $btcBalance,
                'sellAmount' => $safeAmount
            ]);

            // ==========================================================
            // 🧠 SELL DECISION
            // ==========================================================
            $shouldSell = (
               $profitPercent >= $targetPercent ||
                ($profitPercent > ($targetPercent / 2) && $rsi > 70)
            );

            if (!$shouldSell) {
                Log::info('Holding position');
                return; // 🚫 STOP HERE — NO BUY
            }

            // 🚫 Extra safety (avoid dust / invalid orders)
            if ($safeAmount <= 0) {
                Log::warning('Invalid sell amount');
                return;
            }

            Log::info('🔴 SELL SIGNAL CONFIRMED');

            $this->executeTrade('sell', $safeAmount, $currentPrice);

            return; // 🚫 CRITICAL: never continue to BUY
        }

        // ==========================================================
        // 🟢 MODE 2: NO BTC → ONLY BUY LOGIC
        // ==========================================================
        Log::info('🟢 BUY MODE ACTIVE');

        $shouldBuy = (
            $rsi < 40 &&
            $trendUp &&
            $this->isPullback($prices) &&
            $this->isMarketActive($prices)
        );

        if (!$shouldBuy) {
            Log::info('No buy signal');
            return;
        }

        Log::info('🟢 BUY SIGNAL');

        // ===============================
        // 💵 TRADE SIZE (FIXED LOGIC)
        // ===============================
        $minBuyUsd = (float) Setting::get('bot_min_buy_usd', 5);
        $buffer = 1;

        // 🚫 balance check
        if ($usdt < ($minBuyUsd + $buffer)) {
            Log::warning('Balance too low for configured minimum buy', [
                'balance' => $usdt,
                'required' => $minBuyUsd + $buffer
            ]);
            return;
        }

        // 🎯 risk (only applies when balance is large enough)
        $maxRisk = $usdt * 0.1;

        // ✅ enforce minimum FIRST
        if ($minBuyUsd > $maxRisk) {
            // small balance → ignore risk, use minimum
            $tradeValue = $minBuyUsd;
        } else {
            // normal case → apply risk safely
            $tradeValue = min($maxRisk, $usdt);
        }

        // 🔐 final safety clamp
        $tradeValue = min($tradeValue, $usdt);

        // 🔍 debug log
        Log::info('TRADE SIZE FIXED', [
            'usdt' => $usdt,
            'minBuyUsd' => $minBuyUsd,
            'maxRisk' => $maxRisk,
            'final' => $tradeValue
        ]);

        // 🟢 use quoteSize
        $amount = round($tradeValue, 2);
    // ===============================
    // 🟢 EXECUTE BUY
    // ===============================
        $this->executeTrade('buy', $amount, $currentPrice);
    }

    // ===============================
    // 💱 EXECUTE TRADE (SMART RETRY)
    // ===============================
    private function executeTrade(string $side, float $amount, float $price)
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            $attempt++;

            Log::info("Attempt {$attempt}", compact('side', 'amount'));

            $response = $this->safeApiCall(
                fn() => $this->bitget->placeOrder('BTCUSDT', $side, $amount, $price)
            );

            Log::info('API RESPONSE', $response ?? []);

            if ($response && ($response['code'] ?? '') === '00000') {

                $orderId = $response['data']['orderId'] ?? null;

                // ===============================
                // 💾 SAVE TRADE
                // ===============================
                $this->storeTrade($side, $price, $amount, $orderId);

                // ===============================
                // 📲 TELEGRAM NOTIFY
                // ===============================
                $this->notify("✅ {$side} executed\nPrice: {$price}\nAmount: {$amount} \n Response: " . json_encode($response));

                // cache for logic
                if ($side === 'buy') {
                    cache(['last_buy_price' => $price], 300);
                    cache(['last_buy_time' => now()], 300);
                }

                return true;
            }

            if (($response['code'] ?? '') === '45110') {
                Log::warning('Minimum amount error');
                return false;
            }

            sleep(2);
        }

        $this->notify("❌ {$side} FAILED  \nPrice: {$price}\nAmount: {$amount} \n Response: " . json_encode($response)  );   

        return false;
    }

    // ===============================
    // 🔧 FORMAT AMOUNT (PRECISION SAFE)
    // ===============================
    private function formatAmount(float $amount): float
    {
        $step = 0.000001; // can be dynamic later
        return floor($amount / $step) * $step;
    }

    // ===============================
    // 📊 HELPERS
    // ===============================
    public function getPrices(): array
    {
        return $this->safeApiCall(fn() => $this->bitget->getCandles()) ?? [];
    }

    private function movingAverage(array $data, int $period): float
    {
        return collect($data)->take(-$period)->avg();
    }

    private function calculateRSI(array $prices, int $period = 14): float
    {
        $gains = 0;
        $losses = 0;

        for ($i = count($prices) - $period; $i < count($prices) - 1; $i++) {
            $diff = $prices[$i + 1] - $prices[$i];
            if ($diff > 0) $gains += $diff;
            else $losses += abs($diff);
        }

        if ($losses == 0) return 100;

        $rs = $gains / $losses;
        return 100 - (100 / (1 + $rs));
    }

    private function isPullback(array $prices): bool
    {
        return end($prices) < $prices[count($prices) - 2];
    }

    private function isOverextended(array $prices): bool
    {
        $current = end($prices);
        $ma50 = $this->movingAverage($prices, 50);
        return ($current - $ma50) / $ma50 > 0.01;
    }

    private function isMarketActive(array $prices): bool
    {
        $range = max($prices) - min($prices);
        return $range / end($prices) > 0.002;
    }

    private function getLastTrade()
    {
        return Trade::latest()->first();
    }

    private function hasOpenPosition(): bool
    {
        $last = $this->getLastTrade();
        return $last && $last->side === 'buy';
    }

    private function lastTradeWasLoss(): bool
    {
        $pairs = \App\Support\TradeAnalyzer::latestPairs(1);
        return !empty($pairs) && $pairs[0]['profit'] < 0;
    }

    private function safeApiCall(callable $callback)
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return null;
        }
    }

    private function isApiHealthy(): bool
    {
        return $this->safeApiCall(fn() => $this->bitget->getPrice()) > 0;
    }

    private function notify(string $message, string $type = 'info'): void
    {
        Log::info($message);

        // if (!in_array($type, ['trade', 'error', 'alert','info'])) return;

        try {
            app(\App\Services\TelegramService::class)->send($message);
        } catch (\Exception $e) {
            Log::error('Telegram failed');
        }
    }
    // protected function notify(string $message, string $type = 'info'): void
    // {
    //     try {
    //         app(\App\Services\TelegramService::class)->send($message);
    //     } catch (\Exception $e) {
    //         Log::error('Telegram failed', [
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }
    // ===============================
    // 📊 DASHBOARD
    // ===============================
    public function getDecision(): string
    {
        if ($this->hasOpenPosition()) return 'sell';

        $prices = $this->getPrices();

        if (count($prices) < 50) return 'wait';

        $rsi = $this->calculateRSI($prices);
        $ma10 = $this->movingAverage($prices, 10);
        $ma50 = $this->movingAverage($prices, 50);

        if ($rsi < 40 && $ma10 > $ma50) return 'buy';
        if ($rsi > 65) return 'sell';

        return 'wait';
    }

    public function getSignal(array $prices): ?string
    {
        if (count($prices) < 50) return null;

        $rsi = $this->calculateRSI($prices);
        $ma10 = $this->movingAverage($prices, 10);
        $ma50 = $this->movingAverage($prices, 50);

        if ($rsi < 40 && $ma10 > $ma50) return 'buy';
        if ($rsi > 65) return 'sell';

        return 'wait';
    }

    // ===============================
// 📊 MARKET SUMMARY (FOR WIDGET)
// ===============================
public function getMarketSummary(): array
{
    $prices = $this->getPrices();

    if (empty($prices) || count($prices) < 50) {
        return [];
    }

    $price = end($prices);
    $rsi = $this->calculateRSI($prices);
    $ma10 = $this->movingAverage($prices, 10);
    $ma50 = $this->movingAverage($prices, 50);

    return [
        'price' => $price,
        'rsi' => $rsi,
        'ma10' => $ma10,
        'ma50' => $ma50,
    ];
}

        // ===============================
    // 🧠 FULL ANALYSIS (FOR BOT WIDGET)
    // ===============================
    public function getFullAnalysis(): array
    {
        $prices = $this->getPrices();

        if (empty($prices) || count($prices) < 50) {
            return [
                'decision' => 'wait',
                'confidence' => 0,
                'notes' => ['Not enough market data'],
                'reason' => 'No signal',
                'last_trade' => null,
            ];
        }

        $rsi = $this->calculateRSI($prices);
        $ma10 = $this->movingAverage($prices, 10);
        $ma50 = $this->movingAverage($prices, 50);

        $trendUp = $ma10 > $ma50;
        $pullback = $this->isPullback($prices);
        $active = $this->isMarketActive($prices);

        // 🎯 Decision (reuse your logic)
        $decision = $this->getDecision();

        // 📊 Confidence score (0–100)
        $score = 0;

        if ($rsi < 40) $score += 30;
        if ($trendUp) $score += 30;
        if ($pullback) $score += 20;
        if ($active) $score += 20;

        // 🧠 Notes (human-readable)
        $notes = [];

        $notes[] = "RSI: " . round($rsi, 2) . " → " . (
            $rsi < 30 ? 'Oversold' :
            ($rsi > 70 ? 'Overbought' : 'Neutral')
        );

        $notes[] = "Trend: " . ($trendUp ? 'Uptrend 📈' : 'Downtrend 📉');
        $notes[] = "Pullback: " . ($pullback ? 'Yes' : 'No');
        $notes[] = "Market: " . ($active ? 'Active' : 'Slow');

        // 🎯 Reason
        $reason = match ($decision) {
            'buy' => 'Good entry after pullback in uptrend',
            'sell' => 'Exit signal or overbought condition',
            default => 'No strong signal',
        };

        return [
            'decision' => $decision,
            'confidence' => min($score, 100),
            'notes' => $notes,
            'reason' => $reason,
            'last_trade' => $this->getLastTrade(),
        ];
    }

    public function getBtcBalance(): float
        {
            $balances = $this->bitget->getBalances();

            if (empty($balances['data'])) {
                return 0;
            }

            $btc = collect($balances['data'])
                ->firstWhere('coin', 'BTC'); // ✅ EXACT MATCH

            if (!$btc) {
                Log::warning('BTC not found in balance', $balances['data']);
                return 0;
            }

            return (float) ($btc['available'] ?? 0); // ✅ THIS IS KEY
        }

        protected function attemptSell(float $btcBalance): void
        {
            $bitget = app(\App\Services\BitgetService::class);

            // Get current price
            $price = $bitget->getBtcPrice();

            if (!$price) {
                Log::warning('No price available, skipping sell');
                return;
            }

            // OPTIONAL: prevent instant sell after buy
            if ($this->recentlyBought()) {
                Log::info('Recently bought, skipping sell');
                return;
            }

            // OPTIONAL: simple profit check (very important)
            if (!$this->isProfitable($price)) {
                Log::info('Not profitable yet, skipping sell', [
                    'price' => $price
                ]);
                return;
            }

            // Execute sell
            $this->sell($btcBalance, $price);
        }

        protected function recentlyBought(): bool
        {
            $lastBuy = cache('last_buy_time');

            if (!$lastBuy) return false;

            return now()->diffInSeconds($lastBuy) < 60; // wait 1 min
        }

        protected function isProfitable(float $currentPrice): bool
        {
            $buyPrice = cache('last_buy_price');

            if (!$buyPrice) return true; // fallback

            return $currentPrice > ($buyPrice * 1.002); // 0.2% profit
        }

        protected function sell(float $btcAmount, float $price): void
        {
            $bitget = app(\App\Services\BitgetService::class);

            Log::info('Placing SELL order', [
                'amount' => $btcAmount,
                'price' => $price
            ]);

            $response = $bitget->placeOrder([
                'symbol' => 'BTCUSDT',
                'side' => 'sell',
                'size' => $btcAmount,
                'price' => $price,
                'type' => 'market', // or limit
            ]);

            Log::info('Sell response', $response);
        }

        protected function storeTrade(string $side, float $price, float $amount, $orderId = null): void
        {
            try {

                \App\Models\Trade::create([
                    'symbol' => 'BTCUSDT',
                    'side' => strtoupper($side),
                    'price' => $price,
                    'quantity' => $amount,
                    'order_id' => $orderId,
                    'executed_at' => now(),
                    // 'source' => 'local',
                ]);

                Log::info('Trade saved', [
                    'side' => $side,
                    'price' => $price,
                    'amount' => $amount
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to save trade', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        public function getTargetAnalysis(): array
        {
            $lastBuy = \App\Models\Trade::where('side', 'buy')->latest()->first();

            if (!$lastBuy) {
                return [];
            }

            $entry = (float) $lastBuy->price;
            $current = $this->bitget->getPrice();

            if (!$current) {
                return [];
            }

        $targetPercent = (float) Setting::get('bot_target_profit', 2);
        $targetMultiplier = 1 + ($targetPercent / 100);

        $target = $entry * $targetMultiplier;     // 🎯 2% target

            $remaining = $target - $current;

            $progress = (($current - $entry) / ($target - $entry)) * 100;

            return [
                'entry' => $entry,
                'current' => $current,
                'target' => $target,
                'remaining' => $remaining,
                'progress' => max(0, min(100, $progress)),
            ];
        }

        public function getAccountSummary(): array
            {
                return [
                    'btc' => $this->getBtcBalance(),
                    'usdt' => $this->getUsdtBalance(),
                ];
            }


            public function getUsdtBalance(): float
        {
            try {
                $balances = $this->bitget->getBalances();

                return (float) collect($balances['data'] ?? [])
                    ->firstWhere('coin', 'USDT')['available'] ?? 0;

            } catch (\Exception $e) {
                Log::error('USDT balance fetch failed', [
                    'error' => $e->getMessage()
                ]);

                return 0;
            }
        }

        public function getDecisionReason()
        {
            $prices = $this->getPrices();

            if (empty($prices)) return 'No market data';

            $rsi = $this->calculateRSI($prices);
            $ma10 = $this->movingAverage($prices, 10);
            $ma50 = $this->movingAverage($prices, 50);

            if ($rsi < 40 && $ma10 > $ma50) {
                return "BUY: RSI low ({$rsi}), uptrend confirmed";
            }

            if ($rsi > 70) {
                return "SELL: RSI high ({$rsi}), overbought";
            }

            return "WAIT: No strong signal";
        }
}