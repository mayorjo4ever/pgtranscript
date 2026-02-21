<?php

namespace App\Services\Bible;

use function app;
use function str_starts_with;

class CommandHandler
{
    public function handle(array $update)
    {
        $text = trim($update['message']['text'] ?? '');

        if (str_starts_with($text, '/start')) {
            app(ReferralService::class)->start($update);
            return;
        }

       if ($text === '/myref' || $text === '👥 My Referrals') {
            app(ReferralService::class)->myRef($update);
            return;
        }

        if ($text === '/invite' || $text === '📤 Invite Friends') {
            app(ReferralService::class)->invite($update);
            return;
        }
        
        ## if (preg_match('/^([1-3]?\s?[A-Za-z]+)\s+(\d+)(?:[:\s](\d+))?$/i', $text)) {
        if (preg_match('/^([1-3]?\s?[A-Za-z]+)\s+(\d+)(?:[:\s](\d+)(?:-(\d+))?)?$/i', $text)) {
            app(BibleService::class)->search($update);
            return;
        }
//        if (preg_match('/^([1-3]?\s?[A-Za-z]+)\s+(\d+):?(\d+)?$/i', $text)) {
//            app(BibleService::class)->search($update);
//            return;
//        }
    }
}