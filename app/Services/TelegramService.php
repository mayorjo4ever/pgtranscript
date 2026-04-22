<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function send($message)
    {
        return Http::withOptions([
            'verify' => false // for your WAMP SSL issue
        ])->get(
            "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN') . "/sendMessage",
            [
                'chat_id' => env('TELEGRAM_CHAT_ID'),
                'text' => $message,
            ]
        );
    }
}