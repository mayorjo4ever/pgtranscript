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

        // Updated regex to handle various formats including "2Kings", "2 Kings", "1 Cor", etc.
        if (preg_match(
            '/^([1-3]?\s*[A-Za-z]+\.?)\s+(\d+)(?::(\d+)(?:-(\d+))?)?$/i',
            $text,
            $matches
        )) {
            // It's a verse reference
            $this->searchByReference($chatId, $matches);
            return;
        }

        // If user is in Bible mode and input doesn't look like a reference, search by keyword
        $userMode = cache()->get("user_mode_{$chatId}");
        if ($userMode === 'bible') {
            $this->searchByKeyword($chatId, $text);
        }
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

        // Clean up book input - remove dots and extra spaces
        $bookInput = str_replace('.', '', $bookInput);
        $bookInput = preg_replace('/\s+/', ' ', $bookInput); // normalize spaces
        $bookInput = trim($bookInput);

        // Search for book
        $book = $this->findBook($bookInput);

        if (!$book) {
            Log::warning('Book not found', ['book_input' => $bookInput]);
            
            // Try searching for partial matches and suggest
            $suggestions = DB::table('kjv_books')
                ->where('name', 'LIKE', "%{$bookInput}%")
                ->orWhere('abbreviation', 'LIKE', "%{$bookInput}%")
                ->limit(5)
                ->pluck('name');
            
            $suggestionText = $suggestions->isEmpty() ? '' : "\n\nDid you mean:\n• " . $suggestions->implode("\n• ");
            
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Book not found: '{$bookInput}'{$suggestionText}\n\nTry:\n• Gen 1:1\n• John 3:16\n• 1 Cor 13:1-13\n• 2 Kings 5:10",
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
            ->limit(20)
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
        $bookInput = strtolower($bookInput);
        
        // First try exact match
        $book = DB::table('kjv_books')
            ->whereRaw('LOWER(name) = ?', [$bookInput])
            ->orWhereRaw('LOWER(abbreviation) = ?', [$bookInput])
            ->first();
        
        if ($book) return $book;

        // Try starts with match
        $book = DB::table('kjv_books')
            ->whereRaw('LOWER(name) LIKE ?', [$bookInput.'%'])
            ->orWhereRaw('LOWER(abbreviation) LIKE ?', [$bookInput.'%'])
            ->first();
        
        if ($book) return $book;

        // Try contains match
        $book = DB::table('kjv_books')
            ->whereRaw('LOWER(name) LIKE ?', ['%'.$bookInput.'%'])
            ->first();
        
        if ($book) return $book;

        // Try common aliases
        $bookAliases = $this->getBookAliases($bookInput);
        if ($bookAliases) {
            foreach ($bookAliases as $alias) {
                $book = DB::table('kjv_books')
                    ->whereRaw('LOWER(name) LIKE ?', [strtolower($alias).'%'])
                    ->orWhereRaw('LOWER(abbreviation) LIKE ?', [strtolower($alias).'%'])
                    ->first();
                
                if ($book) return $book;
            }
        }

        return null;
    }

    private function getBookAliases($input)
    {
        $input = strtolower(trim($input));
        
        $aliases = [
            // Old Testament
            'gen' => ['Genesis'],
            'exod' => ['Exodus'],
            'ex' => ['Exodus'],
            'lev' => ['Leviticus'],
            'num' => ['Numbers'],
            'deut' => ['Deuteronomy'],
            'dt' => ['Deuteronomy'],
            'josh' => ['Joshua'],
            'judg' => ['Judges'],
            'ruth' => ['Ruth'],
            '1 sam' => ['1 Samuel', 'I Samuel', 'First Samuel'],
            '1sam' => ['1 Samuel', 'I Samuel'],
            '2 sam' => ['2 Samuel', 'II Samuel', 'Second Samuel'],
            '2sam' => ['2 Samuel', 'II Samuel'],
            '1 kings' => ['1 Kings', 'I Kings', 'First Kings'],
            '1kings' => ['1 Kings', 'I Kings'],
            '2 kings' => ['2 Kings', 'II Kings', 'Second Kings'],
            '2kings' => ['2 Kings', 'II Kings'],
            '1 chron' => ['1 Chronicles', 'I Chronicles'],
            '1chron' => ['1 Chronicles'],
            '2 chron' => ['2 Chronicles', 'II Chronicles'],
            '2chron' => ['2 Chronicles'],
            'ezra' => ['Ezra'],
            'neh' => ['Nehemiah'],
            'esth' => ['Esther'],
            'job' => ['Job'],
            'ps' => ['Psalms', 'Psalm'],
            'prov' => ['Proverbs'],
            'pr' => ['Proverbs'],
            'eccles' => ['Ecclesiastes'],
            'eccl' => ['Ecclesiastes'],
            'song' => ['Song of Solomon', 'Song of Songs'],
            'isa' => ['Isaiah'],
            'is' => ['Isaiah'],
            'jer' => ['Jeremiah'],
            'lam' => ['Lamentations'],
            'ezek' => ['Ezekiel'],
            'ez' => ['Ezekiel'],
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
            'zec' => ['Zechariah'],
            'mal' => ['Malachi'],
            
            // New Testament
            'matt' => ['Matthew'],
            'mat' => ['Matthew'],
            'mt' => ['Matthew'],
            'mark' => ['Mark'],
            'mk' => ['Mark'],
            'luke' => ['Luke'],
            'lk' => ['Luke'],
            'john' => ['John'],
            'jn' => ['John'],
            'acts' => ['Acts'],
            'rom' => ['Romans'],
            '1 cor' => ['1 Corinthians', 'I Corinthians'],
            '1cor' => ['1 Corinthians'],
            '2 cor' => ['2 Corinthians', 'II Corinthians'],
            '2cor' => ['2 Corinthians'],
            'gal' => ['Galatians'],
            'eph' => ['Ephesians'],
            'phil' => ['Philippians'],
            'php' => ['Philippians'],
            'col' => ['Colossians'],
            '1 thess' => ['1 Thessalonians', 'I Thessalonians'],
            '1thess' => ['1 Thessalonians'],
            '2 thess' => ['2 Thessalonians', 'II Thessalonians'],
            '2thess' => ['2 Thessalonians'],
            '1 tim' => ['1 Timothy', 'I Timothy'],
            '1tim' => ['1 Timothy'],
            '2 tim' => ['2 Timothy', 'II Timothy'],
            '2tim' => ['2 Timothy'],
            'titus' => ['Titus'],
            'tit' => ['Titus'],
            'philem' => ['Philemon'],
            'phlm' => ['Philemon'],
            'heb' => ['Hebrews'],
            'james' => ['James'],
            'jas' => ['James'],
            '1 pet' => ['1 Peter', 'I Peter'],
            '1pet' => ['1 Peter'],
            '2 pet' => ['2 Peter', 'II Peter'],
            '2pet' => ['2 Peter'],
            '1 john' => ['1 John', 'I John'],
            '1john' => ['1 John'],
            '1jn' => ['1 John'],
            '2 john' => ['2 John', 'II John'],
            '2john' => ['2 John'],
            '3 john' => ['3 John', 'III John'],
            '3john' => ['3 John'],
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