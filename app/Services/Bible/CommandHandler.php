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
        $chatId = $update['message']['chat']['id'];

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

        // Handle Hymns menu click - prompt for hymn number
        if ($text === '🎵 Hymns' || $text === '/hymns') {
            app(HymnService::class)->promptForHymn($update);
            return;
        }

        // Handle Read Bible menu click - show instructions
        if ($text === '📖 Read Bible' || $text === '/bible') {
            app(TelegramService::class)->sendMessage([
                'chat_id' => $chatId,
                'text' => "📖 *Bible Reading*\n\nType a Bible reference to read:\n\n" .
                         "Examples:\n" .
                         "• Gen 1:1\n" .
                         "• John 3:16\n" .
                         "• Mat 7:7\n" .
                         "• 1 Cor 13:1-13\n" .
                         "• Psalms 23",
                'parse_mode' => 'Markdown'
            ]);
            return;
        }

        // Try to handle as hymn search first (Hymn 1, Hymn 25, etc.)
        if (app(HymnService::class)->search($update)) {
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

        // Optional: Help message for unrecognized input
        app(TelegramService::class)->sendMessage([
            'chat_id' => $chatId,
            'text' => "ℹ️ I didn't understand that. Try:\n\n" .
                     "📖 Bible: Gen 1:1, John 3:16\n" .
                     "🎵 Hymns: Hymn 1, Hymn 25\n" .
                     "👥 /myref - View referrals\n" .
                     "📤 /invite - Get invite link"
        ]);
    }
}