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
        if (preg_match('/^hymn\s+(\d+)$/i', $text, $matches)) {
            $hymnNumber = (int)$matches[1];
        }
        elseif (preg_match('/^(\d+)$/', $text)) {
            $hymnNumber = (int)$text;
        }
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

            if ($searchResults->count() === 1) {
                $this->displayHymn($chatId, $searchResults->first());
                return true;
            }

            $this->displaySearchResults($chatId, $searchResults, $text);
            return true;
        }

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
        // Send hymn title and number first
        $headerMessage = "🎵 *Hymn {$hymn->number}*\n*{$hymn->title}*";
        
        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $headerMessage,
            'parse_mode' => 'Markdown'
        ]);

        usleep(200000); // 0.2 second delay

        // Split hymn into verses/sections
        $sections = $this->splitHymnIntoSections($hymn->lyrics);

        foreach ($sections as $index => $section) {
            // Remove excessive indentation and clean up the text
            $cleanedSection = $this->cleanupSection($section);
            
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $cleanedSection,
                'parse_mode' => 'Markdown'
            ]);

            // Delay between sections to avoid rate limiting
            if ($index < count($sections) - 1) {
                usleep(300000); // 0.3 seconds between verses
            }
        }

        // Send footer with "Add Note" button after all verses
        $keyboard = app(KeyboardService::class)->hymnButtons($hymn->number);
        
        $footerMessage = "━━━━━━━━━━━━━━━\n" .
                        "Enter another hymn number or search term";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $footerMessage,
            'reply_markup' => $keyboard
        ]);
    }

    private function splitHymnIntoSections($lyrics)
    {
        // Split by double newlines or verse markers
        $sections = [];
        
        // First, split by common patterns
        // Pattern 1: (1), (2), (3) - verse numbers
        // Pattern 2: CHORUS, REFRAIN
        // Pattern 3: Double newlines
        
        $lines = explode("\n", $lyrics);
        $currentSection = [];
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            // Check if this is a new verse/chorus marker
            if (preg_match('/^\((\d+)\)$/', $trimmedLine) || 
                preg_match('/^(CHORUS|REFRAIN|BRIDGE)[:]*$/i', $trimmedLine)) {
                
                // Save previous section if it has content
                if (!empty($currentSection)) {
                    $sections[] = implode("\n", $currentSection);
                    $currentSection = [];
                }
                
                // Start new section with the marker
                $currentSection[] = $trimmedLine;
            }
            // Skip empty lines at the start of a section
            elseif (empty($currentSection) && empty($trimmedLine)) {
                continue;
            }
            // Add line to current section
            else {
                $currentSection[] = $line;
            }
        }
        
        // Add the last section
        if (!empty($currentSection)) {
            $sections[] = implode("\n", $currentSection);
        }
        
        return $sections;
    }

    private function cleanupSection($section)
    {
        // Remove excessive leading/trailing whitespace
        $lines = explode("\n", $section);
        $cleanedLines = [];
        
        foreach ($lines as $line) {
            // Remove leading spaces but keep the content
            $trimmed = ltrim($line);
            
            // Skip completely empty lines at start and end
            if (!empty($trimmed) || !empty($cleanedLines)) {
                $cleanedLines[] = $trimmed;
            }
        }
        
        // Remove trailing empty lines
        while (!empty($cleanedLines) && trim(end($cleanedLines)) === '') {
            array_pop($cleanedLines);
        }
        
        return implode("\n", $cleanedLines);
    }

    private function displaySearchResults($chatId, $results, $searchTerm)
    {
        $message = "🔍 *Found {$results->count()} hymns matching:* _{$searchTerm}_\n\n";
        $message .= "Select a hymn by typing its number:\n\n";

        foreach ($results as $hymn) {
            $title = $hymn->title;
            if (stripos($title, $searchTerm) !== false) {
                $message .= "*{$hymn->number}*. {$title} ✓\n";
            } else {
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