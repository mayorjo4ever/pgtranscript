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

        // Log for debugging
        Log::info('Bible search request', ['text' => $text]);

        // Updated regex to handle various formats
        if (!preg_match(
            '/^([1-3]?\s*[A-Za-z]+\.?)\s+(\d+)(?::(\d+)(?:-(\d+))?)?$/i',
            $text,
            $matches
        )) {
            Log::info('Regex did not match', ['text' => $text]);
            return;
        }

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
                'text' => "❌ Book not found: '{$bookInput}'\n\nTry formats like:\n• Gen 1:1\n• John 3:16\n• 1 Cor 13:1-13"
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
            // Single verse - add navigation buttons
            $keyboard = app(KeyboardService::class)
                ->verseNavigation($book->id, $chapter, $verseStart);

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard
            ]);
        } else {
            // Multiple verses or whole chapter - no navigation
            foreach (str_split($message, 3500) as $chunk) {
                $this->telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $chunk,
                    'parse_mode' => 'Markdown',
                ]);
            }
        }
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
            // Old Testament
            'gen' => ['Genesis'],
            'exod' => ['Exodus'],
            'lev' => ['Leviticus'],
            'num' => ['Numbers'],
            'deut' => ['Deuteronomy'],
            'josh' => ['Joshua'],
            'judg' => ['Judges'],
            'ruth' => ['Ruth'],
            '1 sam' => ['1 Samuel', 'I Samuel'],
            '2 sam' => ['2 Samuel', 'II Samuel'],
            '1 kings' => ['1 Kings', 'I Kings'],
            '2 kings' => ['2 Kings', 'II Kings'],
            '1 chron' => ['1 Chronicles', 'I Chronicles'],
            '2 chron' => ['2 Chronicles', 'II Chronicles'],
            'ps' => ['Psalms', 'Psalm'],
            'prov' => ['Proverbs'],
            'eccles' => ['Ecclesiastes'],
            'song' => ['Song of Solomon', 'Song of Songs'],
            'isa' => ['Isaiah'],
            'jer' => ['Jeremiah'],
            'lam' => ['Lamentations'],
            'ezek' => ['Ezekiel'],
            'dan' => ['Daniel'],
            'hos' => ['Hosea'],
            'joel' => ['Joel'],
            'amos' => ['Amos'],
            'obad' => ['Obadiah'],
            'jonah' => ['Jonah'],
            'mic' => ['Micah'],
            'nah' => ['Nahum'],
            'hab' => ['Habakkuk'],
            'zeph' => ['Zephaniah'],
            'hag' => ['Haggai'],
            'zech' => ['Zechariah'],
            'mal' => ['Malachi'],
            
            // New Testament
            'matt' => ['Matthew'],
            'mark' => ['Mark'],
            'luke' => ['Luke'],
            'john' => ['John'],
            'acts' => ['Acts'],
            'rom' => ['Romans'],
            '1 cor' => ['1 Corinthians', 'I Corinthians'],
            '2 cor' => ['2 Corinthians', 'II Corinthians'],
            'gal' => ['Galatians'],
            'eph' => ['Ephesians'],
            'phil' => ['Philippians'],
            'col' => ['Colossians'],
            '1 thess' => ['1 Thessalonians', 'I Thessalonians'],
            '2 thess' => ['2 Thessalonians', 'II Thessalonians'],
            '1 tim' => ['1 Timothy', 'I Timothy'],
            '2 tim' => ['2 Timothy', 'II Timothy'],
            'titus' => ['Titus'],
            'philem' => ['Philemon'],
            'heb' => ['Hebrews'],
            'james' => ['James'],
            '1 pet' => ['1 Peter', 'I Peter'],
            '2 pet' => ['2 Peter', 'II Peter'],
            '1 john' => ['1 John', 'I John'],
            '2 john' => ['2 John', 'II John'],
            '3 john' => ['3 John', 'III John'],
            'jude' => ['Jude'],
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

        // Get the verse data
        $verseData = DB::table('kjv_verses')
            ->where('book_id', $bookId)
            ->where('chapter', $chapter)
            ->where('verse', $verse)
            ->first();

        // If verse not found, try next chapter or previous chapter
        if (!$verseData) {
            if ($action === 'next') {
                // Try first verse of next chapter
                $chapter = (int)$chapter + 1;
                $verse = 1;
                $verseData = DB::table('kjv_verses')
                    ->where('book_id', $bookId)
                    ->where('chapter', $chapter)
                    ->where('verse', $verse)
                    ->first();
            } elseif ($action === 'prev') {
                // Try last verse of previous chapter
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
            // Reached end of book or beginning
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