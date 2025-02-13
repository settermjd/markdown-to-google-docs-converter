<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Service\MarkdownConverterService;
use PHPUnit\Framework\TestCase;

final class MarkdownConverterServiceTest extends TestCase
{
    public function testCanEscapeCodeBlocksInUploadedMarkdown(): void
    {
         $originalFile = file_get_contents(__DIR__ . "/../_data/files/README.original.md");
         $convertedFile = file_get_contents(__DIR__ . "/../_data/files/README.converted.md");
         $service = new MarkdownConverterService();

         $this->assertSame($convertedFile, $service->escapeCodeBlocks($originalFile));
    }
}
