<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BotController extends Controller
{
    public function status()
    {
        $bot = app(\App\Services\TradingBotService::class);

        $prices = $bot->getPrices();
        $summary = $bot->getMarketSummary();

        $lastTrade = \App\Models\Trade::latest()->first();

        $targetPercent = \App\Models\Setting::get('bot_target_profit', 2);

        $targetPrice = null;

        if ($lastTrade && $lastTrade->side === 'buy') {
            $targetPrice = $lastTrade->price * (1 + $targetPercent / 100);
        }

        return response()->json([
            'price' => $summary['price'] ?? 0,
            'rsi' => $summary['rsi'] ?? 0,
            'last_trade' => $lastTrade,
            'target_price' => $targetPrice,
            'settings' => [
                'target_profit' => $targetPercent,
                'min_buy' => \App\Models\Setting::get('bot_min_buy_usd', 5),
            ]
        ]);
    }
}
