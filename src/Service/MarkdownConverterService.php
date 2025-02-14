<?php

declare(strict_types=1);

namespace App\Service;

use League\CommonMark\CommonMarkConverter;
use Pandoc\Pandoc;

use function preg_replace;

class MarkdownConverterService
{
    public const string REGEX_INLINE             = "/(`)([\w\(\)\/]+)(`)/";
    public const string REGEX_INLINE_REPLACEMENT = "\\`$2\\`";

    public const string REGEX_FENCED_START             = "/^(```[\w]+)$/mi";
    public const string REGEX_FENCED_START_REPLACEMENT = "~~~\n$1";

    public const string REGEX_FENCED_END             = "/^(```)$/mi";
    public const string REGEX_FENCED_END_REPLACEMENT = "$1\n~~~";

    public function __construct(private array $config = []) 
    {
    }

    private function escapeCodeBlocks(string $markdown): string
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

    public function convertMarkdown(string $markdown, string $filename): string
    {
        $convertedMarkdown = new CommonMarkConverter()
            ->convert($this->escapeCodeBlocks($markdown))
            ->getContent();

        // Store the converted Markdown so that it can be converted to ODT
        $tmpFile = tempnam(sys_get_temp_dir(), 'convert');
        $handle = fopen($tmpFile, "w");
        fwrite($handle, $convertedMarkdown);
        fclose($handle);

        $outputFile = $this->config['output_dir'] . "/" . $filename;
        new Pandoc()
            ->from('markdown')
            ->inputFile($tmpFile)
            ->to('odt')
            ->output($outputFile)
            ->option('reference-doc', $this->config['styles'])
            ->run();

        unlink($tmpFile);

        return $outputFile;
    }
}
