<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    public function getUsdToNgn(): float
    {
        $response = Http::withOptions([
            'verify' => false
        ])->get('https://open.er-api.com/v6/latest/USD');

        $data = $response->json();

        return $data['rates']['NGN'] ?? 0;
    }
}