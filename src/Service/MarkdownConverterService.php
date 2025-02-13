<?php

declare(strict_types=1);

namespace App\Service;

final class MarkdownConverterService
{
    public const string REGEX_INLINE = "/(`)([\w\(\)\/]+)(`)/";
    public const string REGEX_INLINE_REPLACEMENT = "\\`$2\\`";

    public const string REGEX_FENCED_START = "/^(```[\w]+)$/mi";
    public const string REGEX_FENCED_START_REPLACEMENT = "~~~\n$1";

    public const string REGEX_FENCED_END = "/^(```)$/mi";
    public const string REGEX_FENCED_END_REPLACEMENT = "$1\n~~~";

    public function escapeCodeBlocks(string $markdown): string
    {
        return preg_replace(
            [
                self::REGEX_INLINE,
                self::REGEX_FENCED_START,
                self::REGEX_FENCED_END,
            ],
            [
                self::REGEX_INLINE_REPLACEMENT,
                self::REGEX_FENCED_START_REPLACEMENT,
                self::REGEX_FENCED_END_REPLACEMENT,
            ],
            $markdown
        );
    }
}
