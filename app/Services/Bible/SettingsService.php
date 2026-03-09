<?php

namespace App\Services\Bible;

use Illuminate\Support\Facades\DB;

class SettingsService
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function showSettings($update)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];

        // Get user settings
        $user = DB::table('telegram_users')
            ->where('telegram_id', $telegramId)
            ->first();

        if (!$user) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ User not found. Please restart the bot with /start'
            ]);
            return;
        }

        $dailyVerseStatus = $user->receive_daily_verse ?? true;
        $statusIcon = $dailyVerseStatus ? '✅' : '❌';
        $statusText = $dailyVerseStatus ? 'Enabled' : 'Disabled';

        $keyboard = app(KeyboardService::class)->settingsMenu($dailyVerseStatus);

        $message = "⚙️ *Settings*\n\n";
        $message .= "📬 *Daily Bible Verses:* {$statusIcon} {$statusText}\n";
        $message .= "Receive verses at 6:00 AM and 6:00 PM daily\n\n";
        $message .= "Click the button below to toggle or type /toggle";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    public function toggleDailyVerse($update)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];
        $firstName = $update['message']['from']['first_name'] ?? 'there';

        // Get current setting
        $user = DB::table('telegram_users')
            ->where('telegram_id', $telegramId)
            ->first();

        if (!$user) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ User not found. Please restart the bot with /start'
            ]);
            return;
        }

        // Toggle the setting
        $currentStatus = $user->receive_daily_verse ?? true;
        $newStatus = !$currentStatus;

        DB::table('telegram_users')
            ->where('telegram_id', $telegramId)
            ->update([
                'receive_daily_verse' => $newStatus,
                'updated_at' => now()
            ]);

        // Send confirmation
        if ($newStatus) {
            $message = "✅ *Daily Verses Enabled!*\n\n";
            $message .= "Hi {$firstName}! 👋\n\n";
            $message .= "You will now receive:\n";
            $message .= "🌅 Morning verse at 6:00 AM\n";
            $message .= "🌙 Evening verse at 6:00 PM\n\n";
            $message .= "God bless you! 🙏";
        } else {
            $message = "❌ *Daily Verses Disabled*\n\n";
            $message .= "You will no longer receive daily verses.\n\n";
            $message .= "You can re-enable anytime by clicking:\n";
            $message .= "⚙️ Settings → Daily Verses";
        }

        $keyboard = app(KeyboardService::class)->mainMenu();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    public function backToMainMenu($update)
    {
        $chatId = $update['message']['chat']['id'];
        $firstName = $update['message']['from']['first_name'] ?? 'there';

        $keyboard = app(KeyboardService::class)->mainMenu();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "👋 Welcome back, {$firstName}!\n\nWhat would you like to do?",
            'reply_markup' => $keyboard
        ]);
    }
}