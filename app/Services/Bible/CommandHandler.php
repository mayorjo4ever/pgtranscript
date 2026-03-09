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
            cache()->forget("user_mode_{$chatId}");
            app(ReferralService::class)->start($update);
            return;
        }

        // Handle Settings
        if ($text === '⚙️ Settings' || $text === '/settings') {
            cache()->forget("user_mode_{$chatId}");
            app(SettingsService::class)->showSettings($update);
            return;
        }

        // Handle Toggle Daily Verse (both button click and command)
        if (str_starts_with($text, '📬 Daily Verses:') || $text === '/toggle') {
            app(SettingsService::class)->toggleDailyVerse($update);
            return;
        }

        // Handle Back to Main Menu
        if ($text === '🔙 Back to Main Menu' || $text === '/menu') {
            cache()->forget("user_mode_{$chatId}");
            app(SettingsService::class)->backToMainMenu($update);
            return;
        }

        // Handle referral commands
        if ($text === '/myref' || $text === '👥 My Referrals') {
            cache()->forget("user_mode_{$chatId}");
            app(ReferralService::class)->myRef($update);
            return;
        }

        // Handle invite command
        if ($text === '/invite' || $text === '📤 Invite Friends') {
            cache()->forget("user_mode_{$chatId}");
            app(ReferralService::class)->invite($update);
            return;
        }

        // Handle Hymns menu click - SET hymn mode
        if ($text === '🎵 Hymns' || $text === '/hymns') {
            cache()->put("user_mode_{$chatId}", 'hymn', 3600);
            app(HymnService::class)->promptForHymn($update);
            return;
        }

        // Handle Read Bible menu click - SET bible mode
        if ($text === '📖 Read Bible' || $text === '/bible') {
            cache()->put("user_mode_{$chatId}", 'bible', 3600);
            app(TelegramService::class)->sendMessage([
                'chat_id' => $chatId,
                'text' => "📖 *Bible Reading Mode*\n\nType a Bible reference to read:\n\n" .
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

        // Get current user mode
        $userMode = cache()->get("user_mode_{$chatId}");

        // If in hymn mode, try to parse as hymn number first
        if ($userMode === 'hymn') {
            if (app(HymnService::class)->search($update)) {
                return;
            }
        }

        // Try to handle as hymn with explicit "Hymn X" format (works in any mode)
        if (preg_match('/^hymn\s+(\d+)$/i', $text)) {
            cache()->put("user_mode_{$chatId}", 'hymn', 3600);
            app(HymnService::class)->search($update);
            return;
        }

        // Handle Bible verse searches (works in any mode)
        if (preg_match('/^([1-3]?\s*[A-Za-z]+\.?)\s+(\d+)(?::(\d+)(?:-(\d+))?)?$/i', $text)) {
            cache()->put("user_mode_{$chatId}", 'bible', 3600);
            app(BibleService::class)->search($update);
            return;
        }

        // If nothing matched, provide helpful feedback based on mode
        if ($userMode === 'hymn') {
            app(TelegramService::class)->sendMessage([
                'chat_id' => $chatId,
                'text' => "🎵 *Hymn Mode Active*\n\n" .
                         "Enter a hymn number (1-500) or:\n\n" .
                         "• Click '📖 Read Bible' to switch to Bible mode\n" .
                         "• Type 'Hymn 25' for a specific hymn"
            ]);
        } elseif ($userMode === 'bible') {
            app(TelegramService::class)->sendMessage([
                'chat_id' => $chatId,
                'text' => "📖 *Bible Mode Active*\n\n" .
                         "Enter a Bible reference or:\n\n" .
                         "Examples:\n" .
                         "• Gen 1:1\n" .
                         "• John 3:16\n" .
                         "• 1 Cor 13:1-13\n\n" .
                         "Click '🎵 Hymns' to switch to Hymn mode"
            ]);
        } else {
            app(TelegramService::class)->sendMessage([
                'chat_id' => $chatId,
                'text' => "ℹ️ *How to use this bot:*\n\n" .
                         "📖 *Bible verses:* Gen 1:1, John 3:16, Mat 7:7\n" .
                         "🎵 *Hymns:* Hymn 1, Hymn 25, or click 🎵 Hymns button\n" .
                         "👥 *Referrals:* /myref\n" .
                         "📤 *Invite:* /invite\n" .
                         "⚙️ *Settings:* /settings",
                'parse_mode' => 'Markdown'
            ]);
        }
    }
}