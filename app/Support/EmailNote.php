<?php

namespace App\Support;

class EmailNote
{
    public static function html(?string $text): string
    {
        // Escape all content; only explicit HTTP(S) URLs become clickable links.
        $parts = preg_split('~(https?://[^\s<>"\x27]+)~u', trim((string) $text), -1, PREG_SPLIT_DELIM_CAPTURE);
        $html = '';
        foreach ($parts ?: [] as $index => $part) {
            if ($index % 2 === 0) {
                $html .= e($part);

                continue;
            }
            $url = rtrim($part, '.,;)');
            $html .= '<a href="'.e($url).'" rel="noopener noreferrer">'.e($url).'</a>'.e(substr($part, strlen($url)));
        }

        return nl2br($html);
    }
}
