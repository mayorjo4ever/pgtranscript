<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
            Log::error('Failed to fetch price', [
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
            ->retry(3, 1000)
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
            Log::error('Failed to fetch candles', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Place Spot Market Order
     */
    public function placeOrder(string $symbol, string $side, float $amount): ?array
    {
        $timestamp = (string)(time() * 1000);
        $method = 'POST';
        $requestPath = '/api/v2/spot/trade/place-order';

        // Format sizes based on Asset side rules
        if (strtolower($side) === 'buy') {
            // BUY -> amount represents total USDT cost allocation
            $size = number_format($amount, 2, '.', '');
        } else {
            // SELL -> amount represents total BTC asset volume
            $size = number_format($amount, 6, '.', '');
        }

        $body = [
            "symbol" => $symbol,
            "side" => strtolower($side),
            "orderType" => "market",
            "force" => "normal",
            "size" => $size
        ];

        $bodyJson = json_encode($body);
        $sign = $this->sign($timestamp, $method, $requestPath, $bodyJson);

        try {
            $response = Http::withOptions([
                'verify' => false
            ])->withHeaders([
                'ACCESS-KEY' => config('services.bitget.key'),
                'ACCESS-SIGN' => $sign,
                'ACCESS-TIMESTAMP' => $timestamp,
                'ACCESS-PASSPHRASE' => config('services.bitget.passphrase'),
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . $requestPath, $body);

            Log::info('ORDER BODY SENT', $body);

            if (!$response->successful()) {
                Log::error('Bitget API HTTP Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $json = $response->json();
            Log::info('ORDER RESPONSE RECEIVED', $json);

            return $json;

        } catch (\Exception $e) {
            Log::error('Order execution failed inside BitgetService', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Fetch Account Balance Details
     */
    public function getBalances(): array
    {
        $timestamp = (string)(time() * 1000);
        $method = 'GET';
        $requestPath = '/api/v2/spot/account/assets';

        $sign = $this->sign($timestamp, $method, $requestPath);

        try {
            $response = Http::withOptions([
                'verify' => false
            ])->withHeaders([
                'ACCESS-KEY' => config('services.bitget.key'),
                'ACCESS-SIGN' => $sign,
                'ACCESS-TIMESTAMP' => $timestamp,
                'ACCESS-PASSPHRASE' => config('services.bitget.passphrase'),
            ])->get($this->baseUrl . $requestPath);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Balances fetch exception: ' . $e->getMessage());
            return [];
        }
    }

    public function parseExecutionTime(string $timestamp): string
    {
        return Carbon::createFromTimestampMs($timestamp)->toDateTimeString();
    }

    public function storeNewTrades(array $orders): void
    {
        foreach ($orders as $order) {
            if (($order['status'] ?? '') !== 'filled') {
                continue;
            }

            if (Trade::where('order_id', $order['orderId'])->exists()) {
                continue;
            }

            Trade::create([
                'symbol' => $order['symbol'],
                'side' => strtoupper($order['side']),
                'price' => $order['price'],
                'quantity' => $order['size'],
                'order_id' => $order['orderId'],
                'executed_at' => Carbon::createFromTimestampMs($order['cTime']),
            ]);
        }
    }

    public function syncTrades(): void
    {
        $orders = $this->getRecentOrders('BTCUSDT');
        $this->storeNewTrades($orders);
    }

    public function getRecentOrders(string $symbol = 'BTCUSDT'): array
    {
        $timestamp = (string) round(microtime(true) * 1000);
        $query = "?symbol={$symbol}&limit=20";
        $path = "/api/v2/spot/trade/orders-history" . $query;

        $signature = $this->sign($timestamp, 'GET', $path);

        try {
            $response = Http::withOptions([
                'verify' => false
            ])->withHeaders([
                'ACCESS-KEY' => config('services.bitget.key'),
                'ACCESS-SIGN' => $signature,
                'ACCESS-TIMESTAMP' => $timestamp,
                'ACCESS-PASSPHRASE' => config('services.bitget.passphrase'),
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . $path);

            return $response->json()['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed retrieving recent historical orders: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Dynamic Internal Request Encryption Generator
     */
    private function sign(string $timestamp, string $method, string $requestPath, string $body = ''): string
    {
        $message = $timestamp . strtoupper($method) . $requestPath . $body;

        return base64_encode(hash_hmac(
            'sha256',
            $message,
            config('services.bitget.secret'),
            true
        ));
    }
}