<?php
namespace App\Services\Bible;

use Telegram\Bot\Keyboard\Keyboard;

class KeyboardService
{
    public function verseNavigation($bookId, $chapter, $verse)
    {
        return Keyboard::make()
            ->inline()
            ->row([
                Keyboard::inlineButton([
                    'text' => '⬅️ Previous',
                    'callback_data' => "prev_{$bookId}_{$chapter}_{$verse}"
                ]),
                Keyboard::inlineButton([
                    'text' => "📖 {$chapter}:{$verse}",
                    'callback_data' => "current_{$bookId}_{$chapter}_{$verse}"
                ]),
                Keyboard::inlineButton([
                    'text' => 'Next ➡️',
                    'callback_data' => "next_{$bookId}_{$chapter}_{$verse}"
                ])
            ]);
            // ->row([
            //     Keyboard::inlineButton([
            //         'text' => '📝 Add Note',
            //         'callback_data' => "addnote_bible_{$bookId}_{$chapter}_{$verse}"
            //     ])
            // ]);
    }

    
    public function hymnButtons($hymnNumber)
    {
        return null; 
        // Keyboard::make()
        //     ->inline()
        //     ->row([
        //         Keyboard::inlineButton([
        //             'text' => '📝 Add Note',
        //             'callback_data' => "addnote_hymn_{$hymnNumber}"
        //         ])
        //     ]);
    }

    public function mainMenu()
    {
        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->row(['📖 Read Bible', '🎵 Hymns'])
            ->row(['📝 My Notes', '👥 My Referrals'])
            ->row(['📤 Invite Friends', '⚙️ Settings']);
    }
        
   public function settingsMenu($dailyVerseEnabled)
    {
        $dailyVerseStatus = $dailyVerseEnabled ? '✅ ON' : '❌ OFF';
        
        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->row(["📬 Daily Verses: {$dailyVerseStatus}"])
            ->row(['🔙 Back to Main Menu']);
    }

}
