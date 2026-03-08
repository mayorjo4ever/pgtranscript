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
                     "You can:\n" .
                     "• Enter a number: *1*, *25*, *100*\n" .
                     "• Search by title: *Amazing Grace*\n" .
                     "• Search by lyrics: *sweet hour*\n\n" .
                     "Click '📖 Read Bible' to switch modes.",
            'parse_mode' => 'Markdown'
        ]);
    }

    public function search($update)
    {
        $chatId = $update['message']['chat']['id'];
        $text = trim($update['message']['text']);

        Log::info('Hymn search request', ['text' => $text]);

        $hymnNumber = null;
        $searchResults = null;

        // First, try to match hymn number formats
        // Match: "Hymn 1", "hymn 25", "HYMN 100"
        if (preg_match('/^hymn\s+(\d+)$/i', $text, $matches)) {
            $hymnNumber = (int)$matches[1];
        }
        // Match: just a number "1", "25", "100"
        elseif (preg_match('/^(\d+)$/', $text)) {
            $hymnNumber = (int)$text;
        }
        // If not a number, search by title or lyrics
        else {
            $searchResults = $this->searchByTitleOrLyrics($text);
            
            if ($searchResults->isEmpty()) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "❌ No hymns found matching: *{$text}*\n\n" .
                             "Try:\n" .
                             "• A hymn number (1-" . Hymn::max('number') . ")\n" .
                             "• Part of the title: *Amazing Grace*\n" .
                             "• Part of the lyrics: *sweet hour*",
                    'parse_mode' => 'Markdown'
                ]);
                return true;
            }

            // If exactly one result, display it
            if ($searchResults->count() === 1) {
                $this->displayHymn($chatId, $searchResults->first());
                return true;
            }

            // If multiple results, show list for user to choose
            $this->displaySearchResults($chatId, $searchResults, $text);
            return true;
        }

        // If we have a hymn number, fetch and display it
        if ($hymnNumber) {
            $hymn = Hymn::where('number', $hymnNumber)->first();

            if (!$hymn) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "❌ Hymn #{$hymnNumber} not found.\n\n" .
                             "Available hymns: 1-" . Hymn::max('number') . "\n\n" .
                             "Try another number, or search by title/lyrics."
                ]);
                return true;
            }

            $this->displayHymn($chatId, $hymn);
            return true;
        }

        return false;
    }

    private function searchByTitleOrLyrics($searchTerm)
    {
        return Hymn::where(function($query) use ($searchTerm) {
            $query->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('lyrics', 'LIKE', "%{$searchTerm}%");
        })
        ->orderBy('number')
        ->get();
    }

    private function displayHymn($chatId, $hymn)
    {
        $message = "🎵 *Hymn {$hymn->number} - {$hymn->title}*\n\n{$hymn->lyrics}\n\n" .
                   "━━━━━━━━━━━━━━━\n" .
                   "Enter another hymn number or search term";

        // Split if too long
        $chunks = str_split($message, 3500);
        foreach ($chunks as $index => $chunk) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $chunk,
                'parse_mode' => 'Markdown'
            ]);
            
            // Small delay between chunks to avoid rate limiting
            if ($index < count($chunks) - 1) {
                usleep(100000); // 0.1 second
            }
        }
    }

    private function displaySearchResults($chatId, $results, $searchTerm)
    {
        $message = "🔍 *Found {$results->count()} hymns matching:* _{$searchTerm}_\n\n";
        $message .= "Select a hymn by typing its number:\n\n";

        foreach ($results as $hymn) {
            // Highlight the search term in title
            $title = $hymn->title;
            if (stripos($title, $searchTerm) !== false) {
                $message .= "*{$hymn->number}*. {$title} ✓\n";
            } else {
                // Found in lyrics
                $message .= "*{$hymn->number}*. {$title}\n";
            }
        }

        $message .= "\n━━━━━━━━━━━━━━━\n";
        $message .= "Type the number to view the full hymn.";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    public function listHymns($update)
    {
        $chatId = $update['message']['chat']['id'];

        $totalHymns = Hymn::count();

        $message = "🎵 *Available Hymns*\n\n";
        $message .= "We have {$totalHymns} hymns available.\n\n";
        $message .= "*Search Options:*\n";
        $message .= "• By number: *1*, *25*, *Hymn 100*\n";
        $message .= "• By title: *Amazing Grace*\n";
        $message .= "• By lyrics: *sweet hour*\n\n";
        $message .= "*Popular Hymns:*\n";

        // Show first 10 hymns as examples
        $popularHymns = Hymn::orderBy('number')->limit(10)->get();
        foreach ($popularHymns as $hymn) {
            $message .= "• {$hymn->number}. {$hymn->title}\n";
        }

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }
}