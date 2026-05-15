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

    // ==========================================================
    // 🤖 BOT STATUS
    // ==========================================================
    if (!Setting::get('bot_enabled', true)) {

        Log::info('Bot disabled');
        $this->notify('⚠️ BOT DISABLED');

        return;
    }

    // ==========================================================
    // 📊 MARKET DATA
    // ==========================================================
    $prices = $this->getPrices();

    $currentPrice = $this->bitget->getPrice();

    if (empty($prices) || !$currentPrice) {

        Log::warning('Invalid market data');

        return;
    }

    // ==========================================================
    // 🧠 INDICATORS
    // ==========================================================
    $rsi = $this->calculateRSI($prices);

    $ma10 = $this->movingAverage($prices, 10);

    $ma50 = $this->movingAverage($prices, 50);

    $trendUp = $ma10 > $ma50;

    // ==========================================================
    // 💰 BALANCES
    // ==========================================================
    $btcBalance = (float) $this->getBtcBalance();

    $usdt = (float) $this->getUsdtBalance();

    Log::info('Balances', [
        'BTC' => $btcBalance,
        'USDT' => $usdt,
    ]);

    // ==========================================================
    // 🛑 GLOBAL COOLDOWN
    // ==========================================================
    $lastTrade = Trade::latest()->first();

    if (
        $lastTrade &&
        now()->diffInSeconds($lastTrade->created_at) < 60
    ) {

        Log::info('⏳ Cooldown active');

        return;
    }

    // ==========================================================
// 🔴 SELL MODE
// ==========================================================
if ($btcBalance > 0.00001) {

    Log::info('📊 SELL MODE ACTIVE');

    // ==========================================================
    // 🧾 LAST BUY
    // ==========================================================
    $lastBuy = Trade::where('side', 'BUY')
        ->latest()
        ->first();

    if (!$lastBuy) {

        Log::warning('No BUY trade found');

        return;
    }

    $entry = (float) $lastBuy->price;

    $profitPercent = (
        ($currentPrice - $entry) / $entry
    ) * 100;

    // ==========================================================
    // 📈 PEAK TRACKING
    // ==========================================================
    $peakPrice = (float) Setting::get(
        'peak_price',
        $currentPrice
    );

    if ($currentPrice > $peakPrice) {

        $peakPrice = $currentPrice;

        Setting::set('peak_price', $peakPrice);
    }

    // ==========================================================
    // 📉 DROP FROM PEAK
    // ==========================================================
    $dropFromPeak = (
        ($peakPrice - $currentPrice) / $peakPrice
    ) * 100;

    // ==========================================================
    // ⚙️ SETTINGS
    // ==========================================================
    $targetProfit = (float) Setting::get(
        'bot_target_profit',
        3
    );

    $stopLoss = (float) Setting::get(
        'bot_stop_loss',
        -2
    );

    $partialTaken = filter_var(
        Setting::get('partial_profit_taken', false),
        FILTER_VALIDATE_BOOLEAN
    );

    // ==========================================================
    // 📊 POSITION LOG
    // ==========================================================
    Log::info('📈 Position', [
        'entry' => round($entry, 2),
        'current' => round($currentPrice, 2),
        'profit%' => round($profitPercent, 2),
        'peak' => round($peakPrice, 2),
        'drop%' => round($dropFromPeak, 2),
        'rsi' => round($rsi, 2),
        'partialTaken' => $partialTaken,
    ]);

    // ==========================================================
    // 🛑 LAYER 1 → STOP LOSS
    // ==========================================================
    if ($profitPercent <= $stopLoss) {

        Log::warning('🛑 STOP LOSS TRIGGERED');

        $this->notify(
            "🛑 STOP LOSS\n"
            . "Entry: {$entry}\n"
            . "Current: {$currentPrice}\n"
            . "Loss: " . round($profitPercent, 2) . "%"
        );

        $this->executeTrade(
            'sell',
            $btcBalance,
            $currentPrice
        );

        Setting::set('peak_price', 0);
        Setting::set('partial_profit_taken', false);
        Setting::set('last_sell_price', $currentPrice);

        return;
    }

    // ==========================================================
    // 🚀 FAILSAFE MAX PROFIT EXIT
    // ==========================================================
    if ($profitPercent >= 4) {

        Log::info('🚀 MAX PROFIT AUTO EXIT');

        $this->notify(
            "🚀 MAX PROFIT EXIT\n"
            . "Profit: " . round($profitPercent, 2) . "%\n"
            . "Price: {$currentPrice}"
        );

        $this->executeTrade(
            'sell',
            $btcBalance,
            $currentPrice
        );

        Setting::set('partial_profit_taken', false);
        Setting::set('peak_price', 0);
        Setting::set('last_sell_price', $currentPrice);

        return;
    }

    // ==========================================================
    // 💰 LAYER 2 → PARTIAL TAKE PROFIT
    // ==========================================================
    if (
        !$partialTaken &&
        $profitPercent >= $targetProfit
    ) {

        if ($btcBalance < 0.00005) {

            Log::warning('BTC too small for partial sell');

            return;
        }

        Log::info('💰 PARTIAL PROFIT TAKEN');

        // sell 70%
        $sellAmount = round($btcBalance * 0.7, 8);

        $this->notify(
            "💰 PARTIAL SELL\n"
            . "Profit: " . round($profitPercent, 2) . "%\n"
            . "Sold: 70%"
        );

        $this->executeTrade(
            'sell',
            $sellAmount,
            $currentPrice
        );

        Setting::set('partial_profit_taken', true);

        Setting::set('last_sell_price', $currentPrice);

        return;
    }

    // ==========================================================
    // 📉 LAYER 3 → TRAILING EXIT
    // ==========================================================
    if (
        $partialTaken &&
        $profitPercent >= 1.5 &&
        $dropFromPeak >= 0.2
    ) {

        Log::info('📉 TRAILING STOP EXIT');

        $remainingBtc = (float) $this->getBtcBalance();

        if ($remainingBtc > 0.00001) {

            $this->notify(
                "📉 FINAL EXIT\n"
                . "Profit Locked: "
                . round($profitPercent, 2)
                . "%\n"
                . "Peak Drop: "
                . round($dropFromPeak, 2)
                . "%"
            );

            $this->executeTrade(
                'sell',
                $remainingBtc,
                $currentPrice
            );
        }

        Setting::set('partial_profit_taken', false);

        Setting::set('peak_price', 0);

        Setting::set('last_sell_price', $currentPrice);

        return;
    }

    // ==========================================================
    // ⚠️ MOMENTUM WEAKNESS EXIT
    // ==========================================================
    if (
        $profitPercent >= 2 &&
        $rsi < 50
    ) {

        Log::info('⚠️ MOMENTUM EXIT');

        $this->notify(
            "⚠️ MOMENTUM EXIT\n"
            . "Profit: "
            . round($profitPercent, 2)
            . "%\n"
            . "RSI Weak"
        );

        $this->executeTrade(
            'sell',
            $btcBalance,
            $currentPrice
        );

        Setting::set('partial_profit_taken', false);

        Setting::set('peak_price', 0);

        Setting::set('last_sell_price', $currentPrice);

        return;
    }

    // ==========================================================
    // ⏳ HOLD POSITION
    // ==========================================================
    Log::info('⏳ Holding position');

    return;
}

    // ==========================================================
    // 🟢 BUY MODE
    // ==========================================================
    Log::info('🟢 BUY MODE ACTIVE');

    // ==========================================================
    // 🧠 TRADE SCORE
    // ==========================================================
    $score = $this->getTradeScore($prices);

    // ==========================================================
    // 🛑 RE-ENTRY PROTECTION
    // ==========================================================
    $lastSellPrice = (float) Setting::get(
        'last_sell_price',
        0
    );

    $reentryDrop = (float) Setting::get(
        'bot_reentry_drop',
        1.5
    );

    $reentryPrice = $lastSellPrice * (
        1 - ($reentryDrop / 100)
    );

    $canReenter = (
        $lastSellPrice == 0 ||
        $currentPrice <= $reentryPrice
    );

    // ==========================================================
    // ⏱️ SELL COOLDOWN
    // ==========================================================
    $lastSellTrade = Trade::where('side', 'SELL')
        ->latest()
        ->first();

    $sellCooldownPassed = true;

    if ($lastSellTrade) {

        $sellCooldownPassed =
            now()->diffInMinutes(
                $lastSellTrade->created_at
            ) >= 15;
    }

    // ==========================================================
    // 📉 PULLBACK FILTER
    // ==========================================================
    $strongPullback =
        $currentPrice < ($ma10 * 0.995);

    // ==========================================================
    // 🧠 FINAL BUY DECISION
    // ==========================================================
    $shouldBuy = (
        $score >= 5 &&
        $trendUp &&
        $strongPullback &&
        $canReenter &&
        $sellCooldownPassed
    );

    Log::info('🧠 BUY CHECK', [
        'score' => $score,
        'trendUp' => $trendUp,
        'pullback' => $strongPullback,
        'canReenter' => $canReenter,
        'sellCooldownPassed' => $sellCooldownPassed,
    ]);

     $this->notify(
        "🧠 BUY CHECK \n"
        . "score: {$score}\n"
        . "Score: {$score}"
        . "trendUp: " . ($trendUp ? 'Yes' : 'No') . "\n"
        . "pullback: " . ($strongPullback ? 'Yes' : 'No') . "\n"
        . "canReenter: " . ($canReenter ? 'Yes' : 'No') . "\n"
        . "sellCooldownPassed: " . ($sellCooldownPassed ? 'Yes' : 'No') . "\n"
        );
    

    if (!$shouldBuy) {

        Log::info('❌ No buy signal');

        return;
    }

    // ==========================================================
    // 💵 POSITION SIZING
    // ==========================================================
    $minBuy = (float) Setting::get(
        'bot_min_buy_usd',
        5
    );

    if ($usdt < $minBuy) {

        Log::warning('Insufficient USDT');
        $this->notify("❌ Insufficient USDT to buy. \n" . 
        "Available: {$usdt} \n" .
         "Required: {$minBuy}");
        return;
    }

    $riskPercent = 0.03;

    $tradeValue = max(
        $usdt * $riskPercent,
        $minBuy
    );

    // ==========================================================
    // 🟢 EXECUTE BUY
    // ==========================================================
    $this->executeTrade(
        'buy',
        $tradeValue,
        $currentPrice
    );

    Setting::set('last_buy_price', $currentPrice);

    Setting::set('peak_price', $currentPrice);

    Setting::set('partial_profit_taken', false);

    $this->notify(
        "🟢 BUY EXECUTED\n"
        . "Price: {$currentPrice}\n"
        . "Score: {$score}"
    );
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
            $lastBuy = \App\Models\Trade::where('side', 'BUY')->latest()->first();

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
