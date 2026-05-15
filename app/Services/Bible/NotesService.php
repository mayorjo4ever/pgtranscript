<?php

namespace App\Services\Bible;

use App\Models\Note;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotesService
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    public function startAddingNote($update)
    {
        $chatId = $update['message']['chat']['id'];

        // Set user to note-adding mode
        cache()->put("user_mode_{$chatId}", 'adding_note', 1800); // 30 minutes
        cache()->put("note_step_{$chatId}", 'date', 1800);
        
        // Pre-fill with today's date
        $todayDate = Carbon::today()->format('Y-m-d');
        cache()->put("note_data_{$chatId}", [
            'date' => $todayDate
        ], 1800);

        $message = "📝 *New Note*\n\n";
        $message .= "📅 *Date:* {$todayDate}\n\n";
        $message .= "Is this date correct?\n";
        $message .= "• Type *yes* to continue\n";
        $message .= "• Or type a different date (YYYY-MM-DD)\n";
        $message .= "• Type /cancel to cancel";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    public function handleNoteInput($update)
    {
        $chatId = $update['message']['chat']['id'];
        $text = trim($update['message']['text']);
        $telegramId = $update['message']['from']['id'];

        // Check if user wants to cancel
        if (strtolower($text) === '/cancel') {
            $this->cancelNote($chatId);
            return;
        }

        // Get current step
        $step = cache()->get("note_step_{$chatId}");
        $noteData = cache()->get("note_data_{$chatId}", []);

        switch ($step) {
            case 'date':
                $this->handleDateInput($chatId, $text, $noteData);
                break;
            
            case 'preacher':
                $this->handlePreacherInput($chatId, $text, $noteData);
                break;
            
            case 'topic':
                $this->handleTopicInput($chatId, $text, $noteData);
                break;
            
            case 'message':
                $this->handleMessageInput($chatId, $telegramId, $text, $noteData);
                break;
        }
    }

    private function handleDateInput($chatId, $text, $noteData)
    {
        if (strtolower($text) === 'yes') {
            // Keep the current date, move to next step
            $this->askForPreacher($chatId, $noteData);
            return;
        }

        // Try to parse the date
        try {
            $date = Carbon::parse($text)->format('Y-m-d');
            $noteData['date'] = $date;
            
            cache()->put("note_data_{$chatId}", $noteData, 1800);
            
            $this->askForPreacher($chatId, $noteData);
        } catch (\Exception $e) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Invalid date format.\n\nPlease enter date as YYYY-MM-DD\nExample: 2026-03-08\n\nOr type *yes* to use today's date.",
                'parse_mode' => 'Markdown'
            ]);
        }
    }

    private function askForPreacher($chatId, $noteData)
    {
        cache()->put("note_step_{$chatId}", 'preacher', 1800);

        $message = "📝 *New Note*\n\n";
        $message .= "📅 Date: {$noteData['date']}\n\n";
        $message .= "👤 *Enter Preacher's Name:*\n";
        $message .= "Example: Pastor John, Rev. Smith\n\n";
        $message .= "Type /cancel to cancel";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handlePreacherInput($chatId, $text, $noteData)
    {
        $noteData['preacher'] = $text;
        cache()->put("note_data_{$chatId}", $noteData, 1800);
        
        $this->askForTopic($chatId, $noteData);
    }

    private function askForTopic($chatId, $noteData)
    {
        cache()->put("note_step_{$chatId}", 'topic', 1800);

        $message = "📝 *New Note*\n\n";
        $message .= "📅 Date: {$noteData['date']}\n";
        $message .= "👤 Preacher: {$noteData['preacher']}\n\n";
        $message .= "📌 *Enter Topic:*\n";
        $message .= "Example: Faith and Grace\n\n";
        $message .= "Type /cancel to cancel";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleTopicInput($chatId, $text, $noteData)
    {
        $noteData['topic'] = $text;
        cache()->put("note_data_{$chatId}", $noteData, 1800);
        
        $this->askForMessage($chatId, $noteData);
    }

    private function askForMessage($chatId, $noteData)
    {
        cache()->put("note_step_{$chatId}", 'message', 1800);

        $message = "📝 *New Note*\n\n";
        $message .= "📅 Date: {$noteData['date']}\n";
        $message .= "👤 Preacher: {$noteData['preacher']}\n";
        $message .= "📌 Topic: {$noteData['topic']}\n\n";
        $message .= "✍️ *Enter your message/notes:*\n";
        $message .= "Write as much as you want.\n\n";
        $message .= "Type /cancel to cancel";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    private function handleMessageInput($chatId, $telegramId, $text, $noteData)
    {
        $noteData['message'] = $text;

        // Save to database
        try {
            Note::create([
                'telegram_id' => $telegramId,
                'date' => $noteData['date'],
                'preacher' => $noteData['preacher'],
                'topic' => $noteData['topic'],
                'message' => $noteData['message'],
            ]);

            $message = "✅ *Note Saved Successfully!*\n\n";
            $message .= "📅 Date: {$noteData['date']}\n";
            $message .= "👤 Preacher: {$noteData['preacher']}\n";
            $message .= "📌 Topic: {$noteData['topic']}\n\n";
            $message .= "View all your notes with /mynotes";

            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown'
            ]);

            // Clear cache
            cache()->forget("user_mode_{$chatId}");
            cache()->forget("note_step_{$chatId}");
            cache()->forget("note_data_{$chatId}");

        } catch (\Exception $e) {
            Log::error('Error saving note', ['error' => $e->getMessage()]);
            
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => '❌ Error saving note. Please try again with /addnote'
            ]);
        }
    }

    private function cancelNote($chatId)
    {
        cache()->forget("user_mode_{$chatId}");
        cache()->forget("note_step_{$chatId}");
        cache()->forget("note_data_{$chatId}");

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => '❌ Note cancelled.'
        ]);
    }

    public function listNotes($update)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];

        $notes = Note::where('telegram_id', $telegramId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($notes->isEmpty()) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "📝 *Your Notes*\n\n" .
                         "You haven't saved any notes yet.\n\n" .
                         "Add a note with /addnote",
                'parse_mode' => 'Markdown'
            ]);
            return;
        }

        $message = "📝 *Your Notes* ({$notes->count()} total)\n\n";

        foreach ($notes->take(10) as $index => $note) {
            $date = Carbon::parse($note->date)->format('M d, Y');
            $message .= ($index + 1) . ". *{$note->topic}*\n";
            $message .= "   📅 {$date} | 👤 {$note->preacher}\n\n";
        }

        if ($notes->count() > 10) {
            $message .= "_Showing 10 of {$notes->count()} notes_\n\n";
        }

        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "Type /viewnote <number> to view full note\n";
        $message .= "Example: /viewnote 1";

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    public function viewNote($update, $noteNumber)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];

        $notes = Note::where('telegram_id', $telegramId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $index = (int)$noteNumber - 1;

        if (!isset($notes[$index])) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Note #{$noteNumber} not found.\n\nUse /mynotes to see all notes.",
                'parse_mode' => 'Markdown'
            ]);
            return;
        }

        $note = $notes[$index];
        $date = Carbon::parse($note->date)->format('M d, Y');

        $message = "📝 *Note #{$noteNumber}*\n\n";
        $message .= "📅 *Date:* {$date}\n";
        $message .= "👤 *Preacher:* {$note->preacher}\n";
        $message .= "📌 *Topic:* {$note->topic}\n\n";
        $message .= "✍️ *Message:*\n{$note->message}\n\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "Saved on: " . $note->created_at->format('M d, Y h:i A');

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown'
        ]);
    }

    public function deleteNote($update, $noteNumber)
    {
        $chatId = $update['message']['chat']['id'];
        $telegramId = $update['message']['from']['id'];

        $notes = Note::where('telegram_id', $telegramId)
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $index = (int)$noteNumber - 1;

        if (!isset($notes[$index])) {
            $this->telegram->sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ Note #{$noteNumber} not found.",
                'parse_mode' => 'Markdown'
            ]);
            return;
        }

        $note = $notes[$index];
        $topic = $note->topic;
        $note->delete();

        $this->telegram->sendMessage([
            'chat_id' => $chatId,
            'text' => "✅ Note deleted: *{$topic}*",
            'parse_mode' => 'Markdown'
        ]);
    }
}