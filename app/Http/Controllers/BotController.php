<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trade;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    public function status()
{
    $bot = app(\App\Services\TradingBotService::class);

    $summary = $bot->getMarketSummary();
    $lastBuy = Trade::where('side', 'buy')->latest()->first();
    $btcBalance = $bot->getBtcBalance();

    $targetPercent = Setting::get('bot_target_profit', 2);

    // ===============================
    // 🎯 TARGET PRICE
    // ===============================
    $targetPrice = null;
    if ($lastBuy) {
        $targetPrice = $lastBuy->price * (1 + $targetPercent / 100);
    }

    // ===============================
    // 🧠 MODE (FIXED)
    // ===============================
    if ($btcBalance > 0.00001) {
        $mode = 'sell';
    } elseif ($lastBuy) {
        $mode = 'wait';
    } else {
        $mode = 'buy';
    }

    // ===============================
    // 💰 PROFIT
    // ===============================
    $profitPercent = 0;

    if ($lastBuy && ($summary['price'] ?? 0)) {
        $profitPercent = (($summary['price'] - $lastBuy->price) / $lastBuy->price) * 100;
    }

    return response()->json([
        'price' => (float) ($summary['price'] ?? 0),
        'rsi' => (float) ($summary['rsi'] ?? 0),

        'mode' => $mode,
        'reason' => $bot->getDecisionReason(),

        'last_trade' => $lastBuy, // ✅ ONLY BUY
        'target_price' => $targetPrice,
        'profit_percent' => round($profitPercent, 2),

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
            $prices = app(\App\Services\TradingBotService::class)->getPrices();

            if (empty($prices)) {
                return response()->json([0]); // prevent crash
            }

            return response()->json($prices);
        }   
}