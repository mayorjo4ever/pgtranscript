<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Trade;
use Carbon\Carbon;

class BitgetService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.bitget.base_url');
    }

    /**
     * Get latest price
     */
    public function getPrice(string $symbol = 'BTCUSDT'): float
        {
            try {
                $response = Http::withOptions([
                    'verify' => false
                ])
                ->retry(3, 1000)
                ->get($this->baseUrl . '/api/v2/spot/market/tickers', [
                    'symbol' => $symbol
                ]);

                $data = $response->json();

                return (float) ($data['data'][0]['lastPr'] ?? 0);

            } catch (\Exception $e) {
                \Log::error('Failed to fetch price', [
                    'error' => $e->getMessage()
                ]);

                return 0;
            }
        }

    /**
     * Get candle data (closing prices)
     */

    public function getCandles(string $symbol = 'BTCUSDT'): array
        {
            try {
                $response = Http::withOptions([
                    'verify' => false
                ])
                ->retry(3, 1000) // retry 3 times
                ->get($this->baseUrl . '/api/v2/spot/market/candles', [
                    'symbol' => $symbol,
                    'granularity' => '1min',
                    'limit' => 100
                ]);

                $data = $response->json();

                return collect($data['data'] ?? [])
                    ->map(fn ($candle) => (float) $candle[4])
                    ->toArray();

            } catch (\Exception $e) {
                \Log::error('Failed to fetch candles', [
                    'error' => $e->getMessage()
                ]);

                return []; // prevent crash
            }
        }

    // public function getCandles(string $symbol = 'BTCUSDT', string $interval = '1min'): array
    // {
    //     $response = Http::withOptions([
    //         'verify' => false
    //     ])->get($this->baseUrl . '/api/v2/spot/market/candles', [
    //         'symbol' => $symbol,
    //         'granularity' => $interval,
    //         'limit' => 100
    //     ]);

    //     $data = $response->json();

    //     return collect($data['data'] ?? [])
    //         ->map(fn ($candle) => (float) $candle[4]) // closing price
    //         ->toArray();
    // }

        public function placeOrder($symbol, $side, $amount)
            {
                $timestamp = (string)(time() * 1000);
                $method = 'POST';
                $requestPath = '/api/v2/spot/trade/place-order';

                // ===============================
                // 🎯 CORRECT BODY
                // ===============================
                if ($side === 'buy') {
                    // ✅ BUY → amount is USDT
                    $size = number_format($amount, 2, '.', '');
                } else {
                    // ✅ SELL → amount is BTC
                    $size = number_format($amount, 6, '.', '');
                }

                $body = [
                    "symbol" => $symbol,
                    "side" => $side,
                    "orderType" => "market",
                    "force" => "normal",
                    "size" => $size
                ];

                $bodyJson = json_encode($body);

                $sign = base64_encode(hash_hmac(
                    'sha256',
                    $timestamp . $method . $requestPath . $bodyJson,
                    config('services.bitget.secret'),
                    true
                ));

                try {
                    $response = \Illuminate\Support\Facades\Http::withOptions([
                        'verify' => false
                    ])->withHeaders([
                        'ACCESS-KEY' => config('services.bitget.key'),
                        'ACCESS-SIGN' => $sign,
                        'ACCESS-TIMESTAMP' => $timestamp,
                        'ACCESS-PASSPHRASE' => config('services.bitget.passphrase'),
                        'Content-Type' => 'application/json'
                    ])->post($this->baseUrl . $requestPath, $body);

                    \Log::info('ORDER BODY', $body);

                    if (!$response->successful()) {
                        \Log::error('HTTP Error', [
                            'status' => $response->status(),
                            'body' => $response->body()
                        ]);
                        return null;
                    }

                    $json = $response->json();

                    \Log::info('ORDER SUCCESS', $json);

                    return $json;

                } catch (\Exception $e) {
                    \Log::error('Order failed', [
                        'error' => $e->getMessage()
                    ]);
                    return null;
                }
            }

    public function getBalances(): array
        {
            $timestamp = (string)(time() * 1000);
            $method = 'GET';
            $requestPath = '/api/v2/spot/account/assets';

            $sign = base64_encode(hash_hmac(
                'sha256',
                $timestamp . $method . $requestPath,
                config('services.bitget.secret'),
                true
            ));

            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false
            ])->withHeaders([
                'ACCESS-KEY' => config('services.bitget.key'),
                'ACCESS-SIGN' => $sign,
                'ACCESS-TIMESTAMP' => $timestamp,
                'ACCESS-PASSPHRASE' => config('services.bitget.passphrase'),
            ])->get($this->baseUrl . $requestPath);

            return $response->json();
        }

    // app/Services/BitgetService.php
 
    public function parseExecutionTime(string $timestamp): string
    {
        return Carbon::createFromTimestampMs($timestamp)
            ->toDateTimeString();
    }



    public function storeNewTrades(array $orders)
    {
        foreach ($orders as $order) {

            if ($order['status'] !== 'filled') {
                continue;
            }

            // جلوگیری از duplicate
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
        }
    }

    public function syncTrades()
        {
            $bitget = app(BitgetService::class);

            $orders = $bitget->getRecentOrders('BTCUSDT');

            $this->storeNewTrades($orders);
        }

        private function sign($timestamp, $method, $requestPath, $body = '')
        {
            $message = $timestamp . strtoupper($method) . $requestPath . $body;

            return base64_encode(hash_hmac(
                'sha256',
                $message,
                config('services.bitget.secret'),
                true
            ));
        }

        public function getRecentOrders(string $symbol = 'BTCUSDT')
        {
            $timestamp = (string) round(microtime(true) * 1000);

            $query = "?symbol={$symbol}&limit=20";

            $path = "/api/v2/spot/trade/orders-history" . $query;

            $signature = $this->sign($timestamp, 'GET', $path);

            $response = Http::withHeaders([
                'ACCESS-KEY' => config('services.bitget.key'),
                'ACCESS-SIGN' => $signature,
                'ACCESS-TIMESTAMP' => $timestamp,
                'ACCESS-PASSPHRASE' => config('services.bitget.passphrase'),
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . $path);

            return $response->json()['data'] ?? [];
        }
}