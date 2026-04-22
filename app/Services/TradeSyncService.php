<?php

namespace App\Services;

use App\Models\Trade;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TradeSyncService
{
    public function sync(): void
    {
        $bitget = app(BitgetService::class);

        $orders = $bitget->getRecentOrders('BTCUSDT');

        Log::info('Bitget raw orders', [
            'orders' => $orders
        ]);

        foreach ($orders as $order) {

            // Only filled trades
            if (($order['status'] ?? '') !== 'filled') {
                continue;
            }

            // Prevent duplicates
            if (Trade::where('order_id', $order['orderId'])->exists()) {
                continue;
            }

            Trade::create([
                'symbol' => $order['symbol'],
                'side' => $order['side'],
                'price' => $order['price'],
                'quantity' => $order['size'],
                'order_id' => $order['orderId'],
                'executed_at' => Carbon::createFromTimestampMs($order['cTime']),
            ]);

            Log::info('Trade synced', [
                'order_id' => $order['orderId'],
            ]);
        }
    }
}