<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TradingBotService;
use App\Models\Setting;
use App\Models\Trade;

class Telegram2Controller extends Controller
{
    public function webhook(Request $request)
    {
        $message = $request->input('message.text');
        $chatId = $request->input('message.chat.id');

        if (!$message) return response()->json(['ok' => true]);

        $bot = app(TradingBotService::class);

        switch (true) {

            case str_starts_with($message, '/start'):
                $this->send($chatId, "🤖 Bot connected!");
                break;

            case str_starts_with($message, '/balance'):
                $btc = $bot->getBtcBalance();
                $usdt = $bot->getUsdtBalance();

                $this->send($chatId, "💰 BTC: $btc\nUSDT: $usdt");
                break;

            case str_starts_with($message, '/status'):
                $price = $bot->bitget->getPrice();
                $pnl = $bot->getPnL();

                $this->send($chatId, "📊 Price: $price\n💰 PnL: $pnl");
                break;

            case str_starts_with($message, '/profit'):
                $value = explode(' ', $message)[1] ?? null;

                if (!$value) {
                    $this->send($chatId, "Usage: /profit 2");
                    break;
                }

                Setting::set('bot_target_profit', $value);
                $this->send($chatId, "✅ Profit set to $value%");
                break;

            case str_starts_with($message, '/minbuy'):
                $value = explode(' ', $message)[1] ?? null;

                if (!$value) {
                    $this->send($chatId, "Usage: /minbuy 5");
                    break;
                }

                Setting::set('bot_min_buy_usd', $value);
                $this->send($chatId, "✅ Min Buy set to $value USDT");
                break;

            case str_starts_with($message, '/run'):
                $bot->handle();
                $this->send($chatId, "🚀 Bot executed");
                break;

            case str_starts_with($message, '/stop'):
                Setting::set('bot_enabled', false);
                $this->send($chatId, "⛔ Bot stopped");
                break;

            case str_starts_with($message, '/startbot'):
                Setting::set('bot_enabled', true);
                $this->send($chatId, "▶️ Bot started");
                break;

            case str_starts_with($message, '/trades'):
                $trades = Trade::latest()->limit(5)->get();

                $msg = "📜 Last Trades:\n";

                foreach ($trades as $t) {
                    $msg .= strtoupper($t->side) . " @ {$t->price}\n";
                }

                $this->send($chatId, $msg);
                break;

            default:
                $this->send($chatId, "❓ Unknown command");
        }

        return response()->json(['ok' => true]);
    }

    private function send($chatId, $text)
        {
            try {
                $token = env('TELEGRAM_BOT_TOKEN');

                // ✅ sanitize message
                $safeText = str_replace(
                    ['_', '*', '[', ']', '(', ')'],
                    '',
                    $text
                );

                file_get_contents(
                    "https://api.telegram.org/bot{$token}/sendMessage?" .
                    http_build_query([
                        'chat_id' => $chatId,
                        'text' => $safeText
                    ])
                );

            } catch (\Throwable $e) {
                \Log::error('Telegram send failed', [
                    'error' => $e->getMessage(),
                    'text' => $text
                ]);
            }
        }
}