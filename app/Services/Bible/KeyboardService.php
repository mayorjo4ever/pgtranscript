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
    }

    public function mainMenu()
    {
        return Keyboard::make()
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->row(['📖 Read Bible', '🔍 Search'])
            ->row(['📚 Books', '⭐ Favorites'])
            ->row(['🎵 Hymns', '👥 My Referrals'])
            ->row(['📤 Invite Friends']);
    }
}

/**
namespace App\Services\Bible;

use function GuzzleHttp\json_encode;

class KeyboardService
{
    public function mainMenu()
    {
        return json_encode([
            'keyboard' => [
                [['text' => '📖 Search Verse']],
                [['text' => '👥 My Referrals'], ['text' => '📤 Invite Friends']]
            ],
            'resize_keyboard' => true
        ]);
    }

    public function verseNavigation($bookId, $chapter, $verse)
    {
        return json_encode([
            'inline_keyboard' => [
                [
                    [
                        'text' => '⬅️ Prev',
                        'callback_data' => "prev_{$bookId}_{$chapter}_{$verse}"
                    ],
                    [
                        'text' => '➡️ Next',
                        'callback_data' => "next_{$bookId}_{$chapter}_{$verse}"
                    ]
                ]
            ]
        ]);
    }
}**/