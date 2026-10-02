<?php

namespace App\Services;

class ModerationService
{
    public function containsBannedWords(?string $text): bool
    {
        if (! $text) {
            return false;
        }
        $words = config('marketplace.banned_words', ['wire transfer', 'western union']);
        $hay = mb_strtolower($text);
        foreach ($words as $word) {
            if ($word !== '' && str_contains($hay, mb_strtolower($word))) {
                return true;
            }
        }

        return false;
    }

    public function detectsOffPlatform(?string $text): bool
    {
        if (! $text) {
            return false;
        }
        $patterns = [
            '/\bwhatsapp\b/i',
            '/\btelegram\b/i',
            '/\bpaypal\.me\b/i',
            '/\b\d{10,}\b/',
            '/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }

        return false;
    }
}
