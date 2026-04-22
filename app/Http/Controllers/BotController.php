<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BotController extends Controller
{
    public function status()
{
    $bot = app(\App\Services\TradingBotService::class);

    $summary = $bot->getMarketSummary();
    $lastTrade = \App\Models\Trade::latest()->first();

    $targetPercent = \App\Models\Setting::get('bot_target_profit', 2);

    $targetPrice = null;

    // ===============================
    // 🎯 TARGET PRICE
    // ===============================
    if ($lastTrade && $lastTrade->side === 'buy') {
        $targetPrice = $lastTrade->price * (1 + $targetPercent / 100);
    }

    // ===============================
    // 🧠 DETERMINE MODE (REAL LOGIC)
    // ===============================
    $btcBalance = $bot->getBtcBalance();
    $mode = 'wait';

    if ($btcBalance > 0.00001) {
        $mode = 'sell'; // holding position
    } elseif ($lastTrade && $lastTrade->side === 'sell') {
        $mode = 'buy'; // ready for next entry
    } else {
        $mode = 'wait';
    }

    return response()->json([
        'price' => $summary['price'] ?? 0,
        'rsi' => $summary['rsi'] ?? 0,
        'mode' => $mode, // ✅ NEW
        'last_trade' => $lastTrade,
        'target_price' => $targetPrice,
        'settings' => [
            'target_profit' => $targetPercent,
            'min_buy' => \App\Models\Setting::get('bot_min_buy_usd', 5),
        ]
    ]);
}

    public function trades()
        {
            return \App\Models\Trade::latest()
                ->limit(20)
                ->get()
                ->map(function ($trade) {
                    return [
                        'side' => strtoupper($trade->side),
                        'price' => (float) $trade->price,
                        'amount' => (float) $trade->amount,
                        'created_at' => $trade->created_at->format('Y-m-d H:i:s'),
                    ];
                });
        }
}
