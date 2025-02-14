<?php

declare(strict_types=1);

namespace AppTest\Service;

use App\Service\MarkdownConverterService;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

final class MarkdownConverterServiceTest extends TestCase
{
    public const string PHYSICAL_CONVERTED_DIR = "/tmp/converted";

    public function setUp(): void
    {
        if (! file_exists(self::PHYSICAL_CONVERTED_DIR)) {
            mkdir(self::PHYSICAL_CONVERTED_DIR);
        }
    }

    public function tearDown(): void
    {
        if (file_exists(self::PHYSICAL_CONVERTED_DIR) && is_dir(self::PHYSICAL_CONVERTED_DIR)) {
            $files = glob(self::PHYSICAL_CONVERTED_DIR . '/*'); // get all file names
            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    unlink($file); // delete file
                }
            }
            rmdir(self::PHYSICAL_CONVERTED_DIR);
        }
    }

    public function testCanConverMarkdownCorrectly(): void
    {
        $originalFile  = file_get_contents(__DIR__ . "/../_data/files/README.original.md");
        new MarkdownConverterService([
            'styles' => realpath(__DIR__) . "/../_data/files/custom-styles.odt",
            'output_dir' => self::PHYSICAL_CONVERTED_DIR,
        ])->convertMarkdown($originalFile, 'README.original.odt');

        $convertedFile = self::PHYSICAL_CONVERTED_DIR . "/README.original.odt";
        $this->assertSame(17243, filesize($convertedFile));
        $this->assertFileExists($convertedFile);
        $this->assertSame(
            'application/vnd.oasis.opendocument.text', 
            mime_content_type($convertedFile)
        );
    }
}
