<?php
namespace App\Services\Bible;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function app;
use function str_starts_with;

class BibleService
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function search($update)
    {
        $chatId = $update['message']['chat']['id'];
        $text   = trim($update['message']['text']);

        Log::info('Bible search request', ['text' => $text]);

        // Check if it's a verse reference format
        if (preg_match(
            '/^([1-3]?\s*[A-Za-z]+\.?)\s+(\d+)(?::(\d+)(?:-(\d+))?)?$/i',
            $text,
            $matches
        )) {
            // It's a verse reference
            $this->searchByReference($chatId, $matches);
            return;
        }

        // Otherwise, search by keyword in Bible text
        $this->searchByKeyword($chatId, $text);
    }

    private function searchByReference($chatId, $matches)
    {
        $bookInput  = trim($matches[1]);
        $chapter    = (int) $matches[2];
        $verseStart = isset($matches[3]) && $matches[3] !== '' ? (int)$matches[3] : null;
        $verseEnd   = isset($matches[4]) && $matches[4] !== '' ? (int)$matches[4] : null;

        Log::info('Parsed input', [
            'book' => $bookInput,
            'chapter' => $chapter,
            'verse_start' => $verseStart,
            'verse_end' => $verseEnd
        ]);

        // Clean up book input
        $bookInput = str_replace('.', '', $bookInput);
        $bookInput = trim($bookInput);

        // Search for book
        $book = $this->findBook($bookInput);

        if (!$book) {
            Log::warning('Book not found', ['book_input' => $bookInput]);
            
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Book not found: '{$bookInput}'\n\nTry:\n• Gen 1:1\n• John 3:16\n• 1 Cor 13:1-13\n\nOr search by keyword: *faith*, *love*, etc.",
                'parse_mode' => 'Markdown'
            ]);
            return;
        }

        Log::info('Book found', ['book' => $book->name, 'book_id' => $book->id]);

        // Build query
        $query = DB::table('kjv_verses')
            ->where('book_id', $book->id)
            ->where('chapter', $chapter);

        if ($verseStart && $verseEnd) {
            $query->whereBetween('verse', [(int)$verseStart, (int)$verseEnd]);
        } elseif ($verseStart) {
            $query->where('verse', (int)$verseStart);
        }

        $verses = $query->orderBy('verse')->get();

        Log::info('Verses found', ['count' => $verses->count()]);

        if ($verses->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ No verses found for {$book->name} {$chapter}" . 
                         ($verseStart ? ":{$verseStart}" : "") .
                         ($verseEnd ? "-{$verseEnd}" : "")
            ]);
            return;
        }

        // Check if it's a single verse (add navigation buttons)
        $isSingleVerse = $verseStart && !$verseEnd && $verses->count() === 1;

        // Build response
        $title = "{$book->name} {$chapter}";
        if ($verseStart && $verseEnd) {
            $title .= ":{$verseStart}-{$verseEnd}";
        } elseif ($verseStart) {
            $title .= ":{$verseStart}";
        }

        $message = "📖 *{$title}*\n\n";
        
        foreach ($verses as $v) {
            $message .= "*{$v->verse}.* {$v->text}\n\n";
        }

        // Send message with or without navigation buttons
        if ($isSingleVerse) {
            $keyboard = app(KeyboardService::class)
                ->verseNavigation($book->id, $chapter, $verseStart);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard
            ]);
        } else {
            foreach (str_split($message, 3500) as $chunk) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $chunk,
                    'parse_mode' => 'Markdown',
                ]);
            }
        }
    }

    private function searchByKeyword($chatId, $keyword)
    {
        Log::info('Searching Bible by keyword', ['keyword' => $keyword]);

        // Search in verse text
        $verses = DB::table('kjv_verses')
            ->join('kjv_books', 'kjv_verses.book_id', '=', 'kjv_books.id')
            ->where('kjv_verses.text', 'LIKE', "%{$keyword}%")
            ->select('kjv_books.name as book_name', 'kjv_verses.*')
            ->orderBy('kjv_verses.id')
            ->limit(20) // Limit to 20 results
            ->get();

        if ($verses->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ No verses found containing: *{$keyword}*\n\nTry:\n• Different keywords\n• Or a verse reference: Gen 1:1",
                'parse_mode' => 'Markdown'
            ]);
            return;
        }

        $message = "🔍 *Found {$verses->count()} verses containing:* _{$keyword}_\n\n";
        
        foreach ($verses->take(10) as $verse) {
            $reference = "{$verse->book_name} {$verse->chapter}:{$verse->verse}";
            $text = $verse->text;
            
            // Truncate if too long
            if (strlen($text) > 100) {
                $text = substr($text, 0, 100) . '...';
            }
            
            $message .= "📖 *{$reference}*\n{$text}\n\n";
        }

        if ($verses->count() > 10) {
            $message .= "_(Showing first 10 of {$verses->count()} results)_\n\n";
        }

        $message .= "Type a specific reference to read the full verse.";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function findBook($bookInput)
    {
        // First try direct match
        $book = DB::table('kjv_books')
            ->where(function($query) use ($bookInput) {
                $query->where('name', 'like', $bookInput.'%')
                      ->orWhere('name', 'like', '%'.$bookInput.'%')
                      ->orWhere('abbreviation', 'like', $bookInput.'%');
            })
            ->first();

        // If not found, try aliases
        if (!$book) {
            $bookAliases = $this->getBookAliases($bookInput);
            if ($bookAliases) {
                foreach ($bookAliases as $alias) {
                    $book = DB::table('kjv_books')
                        ->where(function($query) use ($alias) {
                            $query->where('name', 'like', $alias.'%')
                                  ->orWhere('abbreviation', 'like', $alias.'%');
                        })
                        ->first();
                    
                    if ($book) break;
                }
            }
        }

        return $book;
    }

    private function getBookAliases($input)
    {
        $input = strtolower(trim($input));
        
        $aliases = [
            'gen' => ['Genesis'],
            'exod' => ['Exodus'],
            'lev' => ['Leviticus'],
            'num' => ['Numbers'],
            'deut' => ['Deuteronomy'],
            'ps' => ['Psalms', 'Psalm'],
            'prov' => ['Proverbs'],
            'isa' => ['Isaiah'],
            'jer' => ['Jeremiah'],
            'matt' => ['Matthew'],
            'rom' => ['Romans'],
            '1 cor' => ['1 Corinthians', 'I Corinthians'],
            '2 cor' => ['2 Corinthians', 'II Corinthians'],
            'gal' => ['Galatians'],
            'eph' => ['Ephesians'],
            'phil' => ['Philippians'],
            'col' => ['Colossians'],
            'heb' => ['Hebrews'],
            'rev' => ['Revelation'],
        ];

        return $aliases[$input] ?? null;
    }

    public function handleCallback($update)
    {
        $callback = $update['callback_query']['data'];
        $chatId   = $update['callback_query']['message']['chat']['id'];
        $messageId = $update['callback_query']['message']['message_id'];

        app(TelegramService::class)->answerCallback([
            'callback_query_id' => $update['callback_query']['id']
        ]);

        if (!str_starts_with($callback, 'next_') && 
            !str_starts_with($callback, 'prev_')) {
            return;
        }

        [$action, $bookId, $chapter, $verse] = explode('_', $callback);
        $verse = (int) $verse;

        if ($action === 'next') {
            $verse++;
        } elseif ($action === 'prev' && $verse > 1) {
            $verse--;
        }

        $verseData = DB::table('kjv_verses')
            ->where('book_id', $bookId)
            ->where('chapter', $chapter)
            ->where('verse', $verse)
            ->first();

        if (!$verseData) {
            if ($action === 'next') {
                $chapter = (int)$chapter + 1;
                $verse = 1;
                $verseData = DB::table('kjv_verses')
                    ->where('book_id', $bookId)
                    ->where('chapter', $chapter)
                    ->where('verse', $verse)
                    ->first();
            } elseif ($action === 'prev') {
                $chapter = (int)$chapter - 1;
                if ($chapter > 0) {
                    $lastVerse = DB::table('kjv_verses')
                        ->where('book_id', $bookId)
                        ->where('chapter', $chapter)
                        ->max('verse');
                    
                    if ($lastVerse) {
                        $verse = $lastVerse;
                        $verseData = DB::table('kjv_verses')
                            ->where('book_id', $bookId)
                            ->where('chapter', $chapter)
                            ->where('verse', $verse)
                            ->first();
                    }
                }
            }
        }

        if (!$verseData) {
            app(TelegramService::class)->answerCallback([
                'callback_query_id' => $update['callback_query']['id'],
                'text' => $action === 'next' ? '✅ End of book reached' : '✅ Beginning of book reached',
                'show_alert' => false
            ]);
            return;
        }

        $book = DB::table('kjv_books')->where('id', $bookId)->first();

        $keyboard = app(KeyboardService::class)
            ->verseNavigation($bookId, $chapter, $verse);

        $newText = "📖 *{$book->name} {$chapter}:{$verse}*\n\n*{$verse}.* {$verseData->text}";

        app(TelegramService::class)->editMessage([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $newText,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
    }
}