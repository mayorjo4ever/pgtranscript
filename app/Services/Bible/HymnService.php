<?php

namespace App\Services\Bible;

use App\Models\Hymn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HymnService
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function promptForHymn($update)
    {
        $chatId = $update['message']['chat']['id'];

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "🎵 *Hymn Selection*\n\nPlease enter a hymn number (1-500):\n\nExample: Hymn 1 or just 1",
            'parse_mode' => 'Markdown'
        ]);

        // Set user state to expect hymn number
        // You can use cache or database to track user state
        cache()->put("user_state_{$chatId}", 'waiting_for_hymn', 300); // 5 minutes
    }

    public function search($update)
    {
        $chatId = $update['message']['chat']['id'];
        $text = trim($update['message']['text']);

        Log::info('Hymn search request', ['text' => $text]);

        // Extract hymn number from various formats
        $hymnNumber = null;

        // Match: "Hymn 1", "hymn 25", "HYMN 100"
        if (preg_match('/^hymn\s+(\d+)$/i', $text, $matches)) {
            $hymnNumber = (int)$matches[1];
        }
        // Match: just a number "1", "25", "100"
        elseif (preg_match('/^(\d+)$/', $text)) {
            // Only if user is in hymn mode
            $userState = cache()->get("user_state_{$chatId}");
            if ($userState === 'waiting_for_hymn') {
                $hymnNumber = (int)$text;
                cache()->forget("user_state_{$chatId}"); // Clear state
            }
        }

        if (!$hymnNumber) {
            return false; // Not a hymn request
        }

        // Fetch hymn from database
        $hymn = Hymn::where('number', $hymnNumber)->first();

        if (!$hymn) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Hymn #{$hymnNumber} not found.\n\nPlease try a number between 1-500."
            ]);
            return true;
        }

        // Send hymn
        $message = "🎵 *Hymn {$hymn->number} - {$hymn->title}*\n\n{$hymn->lyrics}";

        // Split if too long
        foreach (str_split($message, 3500) as $chunk) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $chunk,
                'parse_mode' => 'Markdown'
            ]);
        }

        return true;
    }

    public function listHymns($update)
    {
        $chatId = $update['message']['chat']['id'];

        $totalHymns = Hymn::count();

        $message = "🎵 *Available Hymns*\n\n";
        $message .= "We have {$totalHymns} hymns available.\n\n";
        $message .= "To view a hymn, type:\n";
        $message .= "• Hymn 1\n";
        $message .= "• Hymn 25\n";
        $message .= "• Or just the number: 100\n\n";
        $message .= "Popular Hymns:\n";

        // Show first 10 hymns as examples
        $popularHymns = Hymn::orderBy('number')->limit(10)->get();
        foreach ($popularHymns as $hymn) {
            $message .= "• Hymn {$hymn->number} - {$hymn->title}\n";
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }
}