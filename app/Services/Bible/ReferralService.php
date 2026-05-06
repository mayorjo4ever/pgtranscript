<?php
namespace App\Services\Bible;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Telegram\Bot\FileUpload\InputFile;
use function app;
use function public_path;

class ReferralService
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function start($update)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];
        $firstName = $update['message']['from']['first_name'] ?? '';
        $lastName = $update['message']['from']['last_name'] ?? '';
        $username = $update['message']['from']['username'] ?? null;

        // Extract referrer ID from the start command
        $text = $update['message']['text'] ?? '';
        $referrerId = null;
        
        if (preg_match('/\/start\s+(\d+)/', $text, $matches)) {
            $referrerId = $matches[1];
        }

        // Check if user already exists
        $existingUser = DB::table('telegram_users')
            ->where('telegram_id', $telegramId)
            ->first();

        if (!$existingUser) {
            // Create new user
            DB::table('telegram_users')->insert([
                'telegram_id' => $telegramId,
                'chat_id' => $chatId,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
                'referred_by' => $referrerId && $referrerId != $telegramId ? $referrerId : null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // If they were referred by someone, notify the referrer
            if ($referrerId && $referrerId != $telegramId) {
                $this->notifyReferrer($referrerId, $firstName);
            }
        } else {
            // Update existing user info
            DB::table('telegram_users')
                ->where('telegram_id', $telegramId)
                ->update([
                    'chat_id' => $chatId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'username' => $username,
                    'is_active' => true,
                    'updated_at' => Carbon::now(),
                ]);
        }

        $referralLink = "https://t.me/Theholy_bible_bot?start={$telegramId}";
        
        $caption = "📖 *Welcome {$firstName}!*

            ✨ *Holy Bible KJV & Hymns*

            You can:
            - Search any Bible verse
            - Read full chapters
            - Search by keyword
            - Read verse ranges
            - Navigate verses easily

            📌 *How To Use:* 
            Type references like:
            - Rev 10:7
            - Mal 4:5-6
            - John 3:16
            - Ps 23

            🎯 *Search Only Believe Hymns:*
            - Hymn 100
            - Hymn 25
            - Hymn 1

            📖 *Continue Your Study*\n\n";

        $keyboard = app(KeyboardService::class)->mainMenu();

        app(TelegramService::class)->sendPhoto([
            'chat_id' => $chatId,
            'photo' => InputFile::create(public_path('images/bible.jpg')),    
            'caption' => $caption,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }

    protected function notifyReferrer($referrerId, $newUserName)
    {
        try {
            $referrer = DB::table('telegram_users')
                ->where('telegram_id', $referrerId)
                ->first();

            if ($referrer) {
                $this->telegram->sendMessage([
                    'chat_id' => $referrer->chat_id,
                    'text' => "🎉 Great news! {$newUserName} just joined using your referral link!",
                    'parse_mode' => 'Markdown'
                ]);
            }
        } catch (Exception $e) {
            // Silently fail if notification doesn't work
            \Log::error('Failed to notify referrer: ' . $e->getMessage());
        }
    }

    public function myRef($update)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];
        
        $referrals = DB::table('telegram_users')
            ->where('referred_by', $telegramId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($referrals->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "👥 You have no referrals yet.\n\nShare your referral link to invite friends!"
            ]);
            return;
        }

        $message = "👥 *Your Referrals (" . $referrals->count() . ")*\n\n";
        
        foreach ($referrals as $index => $user) {
            $name = $user->first_name ?? 'Unknown';
            $username = $user->username ? "@{$user->username}" : '';
            $date = Carbon::parse($user->created_at)->format('M d, Y');
            $message .= ($index + 1) . ". {$name} {$username} - {$date}\n";
        }

        foreach (str_split($message, 3500) as $chunk) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $chunk,
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function invite($update)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];
        $firstName = $update['message']['from']['first_name'] ?? '';
        
        $referralLink = "https://t.me/Theholy_bible_bot?start={$telegramId}";
        
        // Count current referrals
        $referralCount = DB::table('telegram_users')
            ->where('referred_by', $telegramId)
            ->count();

        $message = "📤 *Share the Gospel!*\n\n";
        $message .= "Hi {$firstName}! 👋\n\n";
        $message .= "Invite friends to read the Bible:\n\n";
        $message .= "`{$referralLink}`\n\n";
        $message .= "👥 Total Referrals: *{$referralCount}*\n\n";
        $message .= "Share this link with your friends and family! 🙏";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }
}