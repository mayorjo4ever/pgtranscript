<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trade;
use App\Models\Setting;

class BotController extends Controller
{
    public function status()
    {
        $bot = app(\App\Services\TradingBotService::class);

        $summary = $bot->getMarketSummary();
        $lastTrade = Trade::latest()->first();
        $btcBalance = $bot->getBtcBalance();

        $targetPercent = Setting::get('bot_target_profit', 2);

        $account = $bot->getAccountSummary();
        $btcBalance = $account['btc'];
        // ===============================
        // 🎯 TARGET PRICE
        // ===============================
        $targetPrice = null;
        if ($lastTrade && $lastTrade->side === 'buy') {
            $targetPrice = $lastTrade->price * (1 + $targetPercent / 100);
        }

        // ===============================
        // 🧠 MODE
        // ===============================
        $mode = 'wait';
        if ($btcBalance > 0.00001) {
            $mode = 'sell';
        } elseif ($lastTrade && $lastTrade->side === 'sell') {
            $mode = 'buy';
        }

        // ===============================
        // 💰 PROFIT CALCULATION
        // ===============================
        $profitPercent = 0;
        if ($lastTrade && $lastTrade->side === 'buy' && $summary['price']) {
            $profitPercent = (($summary['price'] - $lastTrade->price) / $lastTrade->price) * 100;
        }

        // ===============================
        // 🧠 BOT REASONING
        // ===============================
        $reason = $bot->getDecisionReason();

        return response()->json([
            'price' => (float) ($summary['price'] ?? 0),
            'rsi' => (float) ($summary['rsi'] ?? 0),
            'mode' => $mode,
            'btc_balance' => $btcBalance,
            'profit_percent' => round($profitPercent, 2),
            'reason' => $reason,

            'last_trade' => $lastTrade,
            'target_price' => $targetPrice,

            'settings' => [
                'target_profit' => $targetPercent,
                'min_buy' => Setting::get('bot_min_buy_usd', 5),
            ]
        ]);
    }

    public function trades()
    {
        return Trade::latest()->limit(50)->get()->map(function ($trade) {
            return [
                'side' => strtoupper($trade->side),
                'price' => (float) $trade->price,
                'amount' => (float) $trade->amount,
                'created_at' => $trade->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    // ===============================
    // 💾 SAVE SETTINGS
    // ===============================
    public function settings(Request $request)
    {
        Setting::set('bot_target_profit', $request->bot_target_profit);
        Setting::set('bot_min_buy_usd', $request->bot_min_buy_usd);

        return response()->json(['success' => true]);
    }

    // ===============================
    // 📈 PRICE HISTORY (FOR CHART)
    // ===============================
    public function chart()
    {
        $bot = app(\App\Services\TradingBotService::class);

        return response()->json(
            $bot->getPrices() // array of prices
        );
    }
}