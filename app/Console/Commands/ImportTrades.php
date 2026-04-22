<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ImportTrades extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-trades';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = storage_path('app/trades.csv');

        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = array_combine($header, $row);

            \App\Models\Trade::create([
                'symbol' => $data['symbol'],
                'side' => strtolower($data['side']),
                'price' => (float) $data['price'],
                'quantity' => (float) $data['quantity'],
                'created_at' => $data['created_at'] ?? now(),
                'updated_at' => now(),
            ]);
        }

        $this->info('✅ Trades imported');
    }
}
