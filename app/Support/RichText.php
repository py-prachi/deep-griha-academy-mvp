<?php

namespace App\Support;

class RichText
{
    // Only bold/italic (plus paragraph/line-break structure) are ever
    // produced by the rich-text fields — anything else (scripts, styles,
    // event handler attributes) is stripped regardless of what the client
    // sends, since the browser-side editor can be bypassed with a raw POST.
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u>';

    public static function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = strip_tags($html, self::ALLOWED_TAGS);
        $html = preg_replace('/<(\/?)(p|br|strong|b|em|i|u)\b[^>]*>/i', '<$1$2>', $html);
        $html = trim($html);

        return ($html === '<p><br></p>' || $html === '') ? '' : $html;
    }

    // Data saved before this feature existed is plain text, not HTML — a
    // stray "<" in that text (e.g. "x < y") must not be mistaken for a tag.
    // Only treat a value as rich HTML if one of our own tags is actually present.
    public static function isRichHtml(?string $value): bool
    {
        return $value !== null && preg_match('/<(p|br|strong|b|em|i|u)[\s\/>]/i', $value) === 1;
    }

    // Safe to output with {!! !!} — HTML values are re-sanitized, plain-text
    // legacy values are escaped and line-broken.
    public static function render(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '—';
        }

        return self::isRichHtml($value) ? self::sanitize($value) : nl2br(e($value));
    }
}
