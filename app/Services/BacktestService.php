<?php

namespace App\Services;

class BacktestService
{
    public function run(array $prices): array
    {
        $balance = 1000; // starting capital
        $position = null;
        $trades = [];

        for ($i = 50; $i < count($prices); $i++) {

            $slice = array_slice($prices, 0, $i);
            $price = $prices[$i];

            $rsi = $this->calculateRSI($slice);
            $ma10 = $this->movingAverage($slice, 10);
            $ma50 = $this->movingAverage($slice, 50);

            $trendUp = end($slice) > $slice[count($slice) - 5];

            // BUY
            if (!$position && $rsi < 30 && $trendUp && $ma10 > $ma50) {

                $amount = ($balance * 0.02) / $price;

                $position = [
                    'entry' => $price,
                    'amount' => $amount,
                ];
            }

            // SELL
            if ($position) {

                $entry = $position['entry'];

                $profitPercent = ($price - $entry) / $entry;

                if ($profitPercent >= 0.03 || $profitPercent <= -0.02 || $rsi > 70) {

                    $profit = ($price - $entry) * $position['amount'];

                    $balance += $profit;

                    $trades[] = [
                        'entry' => $entry,
                        'exit' => $price,
                        'profit' => $profit,
                    ];

                    $position = null;
                }
            }
        }

        return [
            'final_balance' => $balance,
            'profit' => $balance - 1000,
            'trades' => $trades,
        ];
    }

    private function movingAverage(array $prices, int $period): float
    {
        return array_sum(array_slice($prices, -$period)) / $period;
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

        $rs = ($gains / $period) / ($losses / $period);

        return 100 - (100 / (1 + $rs));
    }
}