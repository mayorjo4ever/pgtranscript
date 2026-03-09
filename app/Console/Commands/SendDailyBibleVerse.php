<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Services\Bible\TelegramService;

class SendDailyBibleVerse extends Command
{
    protected $signature = 'bible:send-daily-verse {time=morning}';
    protected $description = 'Send daily Bible verse to all active users';

    public function handle()
    {
        $time = $this->argument('time'); // 'morning' or 'evening'
        
        $this->info("Starting to send {$time} Bible verses...");

        // Get a random verse
        $verse = $this->getVerseOfTheDay($time);
        
        if (!$verse) {
            $this->error('No verse found!');
            return;
        }

        // Get all active users
        $users = DB::table('telegram_users')
            ->where('is_active', true)
            ->where('receive_daily_verse', true) // Add this
            ->whereNotNull('chat_id')
            ->get();

        $this->info("Found {$users->count()} active users");

        $successCount = 0;
        $failCount = 0;

        $telegram = app(TelegramService::class);

        foreach ($users as $user) {
            try {
                $greeting = $time === 'morning' 
                    ? "🌅 *Good Morning, {$user->first_name}!*" 
                    : "🌙 *Good Evening, {$user->first_name}!*";

                $message = "{$greeting}\n\n";
                $message .= "📖 *Daily Verse*\n\n";
                $message .= "*{$verse->book_name} {$verse->chapter}:{$verse->verse}*\n\n";
                $message .= "{$verse->text}\n\n";
                $message .= "━━━━━━━━━━━━━━━\n";
                $message .= "Have a blessed day! 🙏";

                $telegram->sendMessage([
                    'chat_id' => $user->chat_id,
                    'text' => $message,
                    'parse_mode' => 'Markdown'
                ]);

                $successCount++;
                $this->info("✓ Sent to {$user->first_name} (ID: {$user->telegram_id})");

                // Small delay to avoid rate limiting (max 30 messages per second)
                usleep(50000); // 0.05 seconds = 20 messages per second

            } catch (\Exception $e) {
                $failCount++;
                $this->error("✗ Failed for {$user->first_name}: " . $e->getMessage());
                
                // If user blocked the bot, mark as inactive
                if (strpos($e->getMessage(), 'blocked') !== false || 
                    strpos($e->getMessage(), 'user is deactivated') !== false) {
                    DB::table('telegram_users')
                        ->where('telegram_id', $user->telegram_id)
                        ->update(['is_active' => false]);
                }
            }
        }

        $this->info("\n✅ Completed!");
        $this->info("Success: {$successCount}");
        $this->info("Failed: {$failCount}");

        return 0;
    }

    private function getVerseOfTheDay($time)
    {
        // Different verse categories for morning and evening
        if ($time === 'morning') {
            // Morning verses - encouraging, strength, new beginnings
            $morningBooks = ['Psalms', 'Proverbs', 'Isaiah', 'Philippians', 'John'];
            
            $verse = DB::table('kjv_verses')
                ->join('kjv_books', 'kjv_verses.book_id', '=', 'kjv_books.id')
                ->whereIn('kjv_books.name', $morningBooks)
                ->where(function($query) {
                    $query->where('kjv_verses.text', 'LIKE', '%strength%')
                          ->orWhere('kjv_verses.text', 'LIKE', '%hope%')
                          ->orWhere('kjv_verses.text', 'LIKE', '%joy%')
                          ->orWhere('kjv_verses.text', 'LIKE', '%peace%')
                          ->orWhere('kjv_verses.text', 'LIKE', '%trust%');
                })
                ->select('kjv_books.name as book_name', 'kjv_verses.*')
                ->inRandomOrder()
                ->first();
        } else {
            // Evening verses - peace, rest, comfort, reflection
            $eveningBooks = ['Psalms', 'Matthew', 'John', 'Philippians', 'Romans'];
            
            $verse = DB::table('kjv_verses')
                ->join('kjv_books', 'kjv_verses.book_id', '=', 'kjv_books.id')
                ->whereIn('kjv_books.name', $eveningBooks)
                ->where(function($query) {
                    $query->where('kjv_verses.text', 'LIKE', '%peace%')
                          ->orWhere('kjv_verses.text', 'LIKE', '%rest%')
                          ->orWhere('kjv_verses.text', 'LIKE', '%comfort%')
                          ->orWhere('kjv_verses.text', 'LIKE', '%love%')
                          ->orWhere('kjv_verses.text', 'LIKE', '%sleep%');
                })
                ->select('kjv_books.name as book_name', 'kjv_verses.*')
                ->inRandomOrder()
                ->first();
        }

        // Fallback to any random verse if no match found
        if (!$verse) {
            $verse = DB::table('kjv_verses')
                ->join('kjv_books', 'kjv_verses.book_id', '=', 'kjv_books.id')
                ->select('kjv_books.name as book_name', 'kjv_verses.*')
                ->inRandomOrder()
                ->first();
        }

        return $verse;
    }
}