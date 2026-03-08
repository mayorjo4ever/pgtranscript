<?php

namespace App\Services\Bible;

use App\Models\Hymn;
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
            'text' => "🎵 *Hymn Mode Activated*\n\n" .
                     "Enter a hymn number (1-500):\n\n" .
                     "Examples:\n" .
                     "• 1\n" .
                     "• 25\n" .
                     "• 100\n" .
                     "• Hymn 50\n\n" .
                     "You can browse multiple hymns.\n" .
                     "Click '📖 Read Bible' to switch to Bible mode.",
            'parse_mode' => 'Markdown'
        ]);
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
            $hymnNumber = (int)$text;
        }

        if (!$hymnNumber) {
            return false; // Not a hymn request
        }

        // Fetch hymn from database
        $hymn = Hymn::where('number', $hymnNumber)->first();

        if (!$hymn) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Hymn #{$hymnNumber} not found.\n\n" .
                         "Available hymns: 1-" . Hymn::max('number') . "\n\n" .
                         "Try another number or click '📖 Read Bible' to switch modes."
            ]);
            return true;
        }

        // Send hymn
        $message = "🎵 *Hymn {$hymn->number} - {$hymn->title}*\n\n{$hymn->lyrics}\n\n" .
                   "━━━━━━━━━━━━━━━\n" .
                   "Enter another hymn number or click '📖 Read Bible'";

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