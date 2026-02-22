<?php
namespace App\Services\Bible;

use function app;
use function str_starts_with;

class CommandHandler
{
    public function handle(array $update)
    {
        // Handle callback queries (button clicks) FIRST
        if (isset($update['callback_query'])) {
            app(BibleService::class)->handleCallback($update);
            return;
        }

        // Handle text messages
        $text = trim($update['message']['text'] ?? '');

        // Handle /start command
        if (str_starts_with($text, '/start')) {
            app(ReferralService::class)->start($update);
            return;
        }

        // Handle referral commands
        if ($text === '/myref' || $text === '👥 My Referrals') {
            app(ReferralService::class)->myRef($update);
            return;
        }

        // Handle invite command
        if ($text === '/invite' || $text === '📤 Invite Friends') {
            app(ReferralService::class)->invite($update);
            return;
        }

        // Handle Bible verse searches
        // This regex handles formats like:
        // - Gen 1:1
        // - 1 Cor 2:1-10
        // - John 3:16
        // - Psalms 23
        if (preg_match('/^([1-3]?\s*[A-Za-z]+\.?)\s+(\d+)(?::(\d+)(?:-(\d+))?)?$/i', $text)) {
            app(BibleService::class)->search($update);
            return;
        }

        // Optional: Add a help message for unrecognized input
        // Uncomment if you want to guide users
        
        $chatId = $update['message']['chat']['id'];
        app(TelegramService::class)->sendMessage([
            'chat_id' => $chatId,
            'text' => "ℹ️ I didn't understand that. Try:\n\n" .
                     "📖 Bible verses: Gen 1:1, John 3:16, 1 Cor 13:1-13\n" .
                     "👥 /myref - View referrals\n" .
                     "📤 /invite - Get invite link"
        ]);
        
    }
}