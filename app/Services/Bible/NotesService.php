<?php

namespace App\Services\Bible;

use App\Models\Note;
use Illuminate\Support\Facades\Log;

class NotesService
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function promptForNote($update, $type, $reference)
    {
        $chatId = $update['message']['chat']['id'] ?? $update['callback_query']['message']['chat']['id'];

        // Store context in cache for when user replies with note
        cache()->put("note_context_{$chatId}", [
            'type' => $type,
            'reference' => $reference
        ], 600); // 10 minutes

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "📝 *Add Note*\n\n" .
                     "Reference: *{$reference}*\n\n" .
                     "Please enter your note in this format:\n\n" .
                     "*Title (optional)*\n" .
                     "Your note content here...\n\n" .
                     "Or just enter the note without a title.\n\n" .
                     "Type /cancel to cancel.",
            'parse_mode' => 'Markdown'
        ]);

        // Set user mode to waiting for note
        cache()->put("user_mode_{$chatId}", 'waiting_for_note', 600);
    }

    public function saveNote($update)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];
        $text = trim($update['message']['text']);

        // Get note context
        $context = cache()->get("note_context_{$chatId}");

        if (!$context) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Note session expired. Please try again.'
            ]);
            return;
        }

        // Parse title and note
        $lines = explode("\n", $text, 2);
        $title = null;
        $note = $text;

        // If there are multiple lines, first line might be title
        if (count($lines) > 1 && strlen($lines[0]) < 100) {
            $title = trim($lines[0]);
            $note = trim($lines[1]);
        }

        // Save to database
        try {
            Note::create([
                'telegram_id' => $telegramId,
                'type' => $context['type'],
                'reference' => $context['reference'],
                'title' => $title,
                'note' => $note
            ]);

            $message = "✅ *Note Saved!*\n\n";
            $message .= "📍 Reference: *{$context['reference']}*\n";
            if ($title) {
                $message .= "📌 Title: *{$title}*\n";
            }
            $message .= "\nYou can view all your notes with /mynotes";

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            // Clear context
            cache()->forget("note_context_{$chatId}");
            cache()->forget("user_mode_{$chatId}");

        } catch (\Exception $e) {
            Log::error('Error saving note', ['error' => $e->getMessage()]);
            
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Error saving note. Please try again.'
            ]);
        }
    }

    public function listNotes($update)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];

        $notes = Note::where('telegram_id', $telegramId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($notes->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "📝 *Your Notes*\n\n" .
                         "You haven't saved any notes yet.\n\n" .
                         "To add a note after reading a verse or hymn, " .
                         "use the '📝 Add Note' button.",
                'parse_mode' => 'Markdown'
            ]);
            return;
        }

        // Group notes by type
        $bibleNotes = $notes->where('type', 'bible');
        $hymnNotes = $notes->where('type', 'hymn');

        $message = "📝 *Your Notes* ({$notes->count()} total)\n\n";

        if ($bibleNotes->isNotEmpty()) {
            $message .= "📖 *Bible Notes ({$bibleNotes->count()})*\n";
            foreach ($bibleNotes->take(5) as $note) {
                $title = $note->title ? " - {$note->title}" : '';
                $message .= "• {$note->reference}{$title}\n";
            }
            if ($bibleNotes->count() > 5) {
                $message .= "_...and " . ($bibleNotes->count() - 5) . " more_\n";
            }
            $message .= "\n";
        }

        if ($hymnNotes->isNotEmpty()) {
            $message .= "🎵 *Hymn Notes ({$hymnNotes->count()})*\n";
            foreach ($hymnNotes->take(5) as $note) {
                $title = $note->title ? " - {$note->title}" : '';
                $message .= "• {$note->reference}{$title}\n";
            }
            if ($hymnNotes->count() > 5) {
                $message .= "_...and " . ($hymnNotes->count() - 5) . " more_\n";
            }
            $message .= "\n";
        }

        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "Type /viewnote <reference> to view a specific note\n";
        $message .= "Example: /viewnote John 3:16";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    public function viewNote($update, $reference)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];

        $notes = Note::where('telegram_id', $telegramId)
            ->where('reference', 'LIKE', "%{$reference}%")
            ->orderBy('created_at', 'desc')
            ->get();

        if ($notes->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ No notes found for: *{$reference}*\n\n" .
                         "Use /mynotes to see all your notes.",
                'parse_mode' => 'Markdown'
            ]);
            return;
        }

        foreach ($notes as $note) {
            $message = "📝 *Note*\n\n";
            $message .= "📍 Reference: *{$note->reference}*\n";
            
            if ($note->title) {
                $message .= "📌 Title: *{$note->title}*\n\n";
            } else {
                $message .= "\n";
            }
            
            $message .= "{$note->note}\n\n";
            $message .= "━━━━━━━━━━━━━━━\n";
            $message .= "📅 " . $note->created_at->format('M d, Y h:i A');

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    public function deleteNote($update, $reference)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];

        $deleted = Note::where('telegram_id', $telegramId)
            ->where('reference', 'LIKE', "%{$reference}%")
            ->delete();

        if ($deleted > 0) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Deleted {$deleted} note(s) for: *{$reference}*",
                'parse_mode' => 'Markdown'
            ]);
        } else {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ No notes found to delete for: *{$reference}*",
                'parse_mode' => 'Markdown'
            ]);
        }
    }
}