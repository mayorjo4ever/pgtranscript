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

        // Bot Enable Check
        if (!Setting::get('bot_enabled', true)) {
            Log::info('Bot disabled');
            Setting::set('bot_enabled', true);
            $this->notify('⚠️ BOT RESET ON');
            return;
        }

        // Market Data Fetching
        $prices = $this->getPrices();
        $currentPrice = $this->bitget->getPrice();

        if (empty($prices) || !$currentPrice) {
            Log::warning('Invalid market data');
            return;
        }

        // Indicators
        $rsi = $this->calculateRSI($prices);
        $ma10 = $this->movingAverage($prices, 10);
        $ma50 = $this->movingAverage($prices, 50);
        $trendUp = $ma10 > $ma50;

        // Account Balances
        $btcBalance = (float) $this->getBtcBalance();
        $usdt = (float) $this->getUsdtBalance();

        Log::info('Balances Checked', [
            'BTC' => $btcBalance,
            'USDT' => $usdt,
        ]);

        // Global Cooldown Safety Check
        $lastTrade = Trade::latest()->first();
        if ($lastTrade && now()->diffInSeconds($lastTrade->created_at) < 60) {
            Log::info('⏳ Cooldown active');
            return;
        }

        // ==========================================================
        // 🔴 SELL MODE (Triggers if real sellable BTC exists)
        // ==========================================================
        if ($btcBalance > 0.0001) { 

            Log::info('📊 SELL MODE ACTIVE');

            $lastBuy = Trade::where('side', 'BUY')->latest()->first();
            
            // If DB trade history was wiped, fallback gracefully using spot price as current baseline
            if (!$lastBuy) {
                Log::warning('No local BUY trade found; default safety baseline assigned to market rate.');
                $entry = $currentPrice;
            } else {
                $entry = (float) $lastBuy->price;
            }

            $profitPercent = (($currentPrice - $entry) / $entry) * 100;

            // Peak Tracking for Trailing Exits
            $peakPrice = (float) Setting::get('peak_price', $currentPrice);
            if ($currentPrice > $peakPrice) {
                $peakPrice = $currentPrice;
                Setting::set('peak_price', $peakPrice);
            }

            $dropFromPeak = (($peakPrice - $currentPrice) / $peakPrice) * 100;

            // Strategy Settings Configurations
            $targetProfit = (float) Setting::get('bot_target_profit', 3);
            $stopLoss = (float) Setting::get('bot_stop_loss', -2);
            $partialTaken = filter_var(Setting::get('partial_profit_taken', false), FILTER_VALIDATE_BOOLEAN);

            Log::info('📈 Position Status Details', [
                'entry' => round($entry, 2),
                'current' => round($currentPrice, 2),
                'profit%' => round($profitPercent, 2),
                'peak' => round($peakPrice, 2),
                'drop%' => round($dropFromPeak, 2),
                'rsi' => round($rsi, 2),
                'partialTaken' => $partialTaken,
            ]);

            // Layer 1: STOP LOSS EXIT
            if ($profitPercent <= $stopLoss) {
                Log::warning('🛑 STOP LOSS TRIGGERED');
                $this->notify("🛑 STOP LOSS\nEntry: {$entry}\nCurrent: {$currentPrice}\nLoss: " . round($profitPercent, 2) . "%");
                $this->executeTrade('sell', $btcBalance, $currentPrice);
                $this->resetSellSettings($currentPrice);
                return;
            }

            // Layer 2: FAILSAFE MAX PROFIT AUTO EXIT
            if ($profitPercent >= 4) {
                Log::info('🚀 MAX PROFIT AUTO EXIT');
                $this->notify("🚀 MAX PROFIT EXIT\nProfit: " . round($profitPercent, 2) . "%\nPrice: {$currentPrice}");
                $this->executeTrade('sell', $btcBalance, $currentPrice);
                $this->resetSellSettings($currentPrice);
                return;
            }

            // Layer 3: PARTIAL TAKE PROFIT ACTIONS (70%)
            if (!$partialTaken && $profitPercent >= $targetProfit) {
                Log::info('💰 PARTIAL PROFIT TAKEN');
                $sellAmount = round($btcBalance * 0.7, 6);

                $this->notify("💰 PARTIAL SELL\nProfit: " . round($profitPercent, 2) . "%\nSold: 70%");
                $this->executeTrade('sell', $sellAmount, $currentPrice);
                Setting::set('partial_profit_taken', true);
                Setting::set('last_sell_price', $currentPrice);
                return;
            }

            // Layer 4: TRAILING STOP EXITS (30% remaining balances)
            if ($partialTaken && $profitPercent >= 1.5 && $dropFromPeak >= 0.2) {
                Log::info('📉 TRAILING STOP EXIT');
                $this->notify("📉 TRAILING STOP EXIT\nProfit Locked: " . round($profitPercent, 2) . "%\nPeak Drop: " . round($dropFromPeak, 2) . "%");
                $this->executeTrade('sell', $btcBalance, $currentPrice);
                $this->resetSellSettings($currentPrice);
                return;
            }

            // Layer 5: MOMENTUM WEAKNESS EXIT TRAPS
            if ($profitPercent >= 2 && $rsi < 50) {
                Log::info('⚠️ MOMENTUM EXIT');
                $this->notify("⚠️ MOMENTUM EXIT\nProfit: " . round($profitPercent, 2) . "%\nRSI Weak");
                $this->executeTrade('sell', $btcBalance, $currentPrice);
                $this->resetSellSettings($currentPrice);
                return;
            }

            Log::info('⏳ Holding active BTC position...');
            return;
        }

        // ==========================================================
        // 🟢 BUY MODE
        // ==========================================================
        Log::info('🟢 BUY MODE ACTIVE');

        $score = $this->getTradeScore($prices);
        
        $lastSellPrice = (float) Setting::get('last_sell_price', 0);
        $reentryDrop = (float) Setting::get('bot_reentry_drop', 1.5);
        $reentryPrice = $lastSellPrice * (1 - ($reentryDrop / 100));
        
        $canReenter = ($lastSellPrice == 0 || $currentPrice <= $reentryPrice);

        $lastSellTrade = Trade::where('side', 'SELL')->latest()->first();
        $sellCooldownPassed = !$lastSellTrade || now()->diffInMinutes($lastSellTrade->created_at) >= 15;

        // FIXED: Relaxed standard to run perfectly in harmony along with active standard trends
        $strongPullback = $currentPrice < ($ma10 * 1.001);

        $shouldBuy = ($score >= 4 && $canReenter && $sellCooldownPassed && $strongPullback);

        Log::info('🧠 BUY LOGIC DIAGNOSTICS', [
            'score' => $score,
            'trendUp' => $trendUp,
            'pullback' => $strongPullback,
            'canReenter' => $canReenter,
            'sellCooldownPassed' => $sellCooldownPassed,
        ]);

        $this->notify(
            "🧠 BUY CHECK\n"
            . "Score: {$score}\n"
            . "TrendUp: " . ($trendUp ? 'Yes' : 'No') . "\n"
            . "Pullback: " . ($strongPullback ? 'Yes' : 'No') . "\n"
            . "CanReenter: " . ($canReenter ? 'Yes' : 'No') . "\n"
            . "SellCooldownPassed: " . ($sellCooldownPassed ? 'Yes' : 'No')
        );

        if (!$shouldBuy) {
            Log::info('❌ No matching structural buy parameters encountered.');
            return;
        }

        // Financial Sizing Rules
        $minBuy = (float) Setting::get('bot_min_buy_usd', 5);
        if ($usdt < $minBuy) {
            Log::warning('Insufficient USDT balances to trigger order execution');
            $this->notify("❌ Insufficient USDT to buy.\nAvailable: {$usdt}\nRequired: {$minBuy}");
            return;
        }

        $riskPercent = 0.03;
        $tradeValue = max($usdt * $riskPercent, $minBuy);

        // Fixed Parameter Count Signature
        $this->executeTrade('buy', $tradeValue, $currentPrice);

        Setting::set('last_buy_price', $currentPrice);
        Setting::set('peak_price', $currentPrice);
        Setting::set('partial_profit_taken', false);

        $this->notify("🟢 BUY EXECUTED\nPrice: {$currentPrice}\nScore: {$score}");
    }

    private function resetSellSettings(float $currentPrice): void
    {
        Setting::set('partial_profit_taken', false);
        Setting::set('peak_price', 0);
        Setting::set('last_sell_price', $currentPrice);
    }

    /**
     * FIXED: Score matrix engine method mapping calculation metrics
     */
    private function getTradeScore(array $prices): int
    {
        if (count($prices) < 50) return 0;

        $score = 0;
        $rsi = $this->calculateRSI($prices);
        $ma10 = $this->movingAverage($prices, 10);
        $ma50 = $this->movingAverage($prices, 50);

        if ($rsi < 40) $score += 2;
        if ($rsi < 30) $score += 1; 
        if ($ma10 > $ma50) $score += 2;
        if ($this->isPullback($prices)) $score += 1;
        if ($this->isMarketActive($prices)) $score += 1;

        return $score;
    }

    private function executeTrade(string $side, float $amount, float $price): bool
    {
        $maxRetries = 3;
        $attempt = 0;
        $response = null;

        while ($attempt < $maxRetries) {
            $attempt++;
            Log::info("Attempt {$attempt}", compact('side', 'amount'));

            // Fixed method parameter structure
            $response = $this->safeApiCall(
                fn() => $this->bitget->placeOrder('BTCUSDT', $side, $amount)
            );

            Log::info('API RESPONSE', $response ?? []);

            if ($response && ($response['code'] ?? '') === '00000') {
                $orderId = $response['data']['orderId'] ?? null;
                $this->storeTrade($side, $price, $amount, $orderId);
                
                // Fixed: Stringified response payload to protect notification integrity
                $this->notify("✅ {$side} executed\nPrice: {$price}\nAmount: {$amount}\nResponse: " . json_encode($response));

                if ($side === 'buy') {
                    cache(['last_buy_price' => $price], 300);
                    cache(['last_buy_time' => now()], 300);
                }
                return true;
            }

            if (($response['code'] ?? '') === '45110') {
                Log::warning('Minimum trading limit order requirement unmet.');
                return false;
            }

            sleep(2);
        }

        $this->notify("❌ {$side} FAILED\nPrice: {$price}\nAmount: {$amount}\nResponse: " . json_encode($response));   
        return false;
    }

    // --- Core Analytic Calculations & Metric Helpers ---
    public function getPrices(): array { return $this->safeApiCall(fn() => $this->bitget->getCandles()) ?? []; }
    private function movingAverage(array $data, int $period): float { return collect($data)->take(-$period)->avg(); }
    private function isPullback(array $prices): bool { return count($prices) > 1 && end($prices) < $prices[count($prices) - 2]; }
    private function isMarketActive(array $prices): bool { if(empty($prices)) return false; $range = max($prices) - min($prices); return ($range / end($prices)) > 0.002; }
    private function safeApiCall(callable $callback) { try { return $callback(); } catch (\Throwable $e) { Log::error($e->getMessage()); return null; } }
    
    private function calculateRSI(array $prices, int $period = 14): float
    {
        if (count($prices) <= $period) return 50;
        $gains = 0; $losses = 0;
        for ($i = count($prices) - $period; $i < count($prices) - 1; $i++) {
            $diff = $prices[$i + 1] - $prices[$i];
            if ($diff > 0) $gains += $diff; else $losses += abs($diff);
        }
        if ($losses == 0) return 100;
        return 100 - (100 / (1 + ($gains / $losses)));
    }

    public function getBtcBalance(): float
    {
        $balances = $this->bitget->getBalances();
        if (empty($balances['data'])) return 0;
        $btc = collect($balances['data'])->firstWhere('coin', 'BTC');
        return (float) ($btc['available'] ?? 0);
    }

    public function getUsdtBalance(): float
    {
        try {
            $balances = $this->bitget->getBalances();
            $usdt = collect($balances['data'] ?? [])->firstWhere('coin', 'USDT');
            return (float) ($usdt['available'] ?? 0);
        } catch (\Exception $e) {
            Log::error('USDT balance fetch failed', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    protected function storeTrade(string $side, float $price, float $amount, $orderId = null): void
    {
        try {
            Trade::create([
                'symbol' => 'BTCUSDT',
                'side' => strtoupper($side),
                'price' => $price,
                'quantity' => $amount,
                'order_id' => $orderId,
                'executed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('DB Save Exception: ' . $e->getMessage());
        }
    }

    private function notify(string $message, string $type = 'info'): void
    {
        Log::info($message);
        try {
            app(\App\Services\TelegramService::class)->send($message);
        } catch (\Exception $e) {
            Log::error('Telegram delivery failed');
        }
    }

    public function getDecision(): string
    {
        $prices = $this->getPrices();
        if (count($prices) < 50) return 'wait';
        $rsi = $this->calculateRSI($prices);
        $ma10 = $this->movingAverage($prices, 10);
        $ma50 = $this->movingAverage($prices, 50);

        if ($rsi < 40 && $ma10 > $ma50) return 'buy';
        if ($rsi > 65) return 'sell';
        return 'wait';
    }
}