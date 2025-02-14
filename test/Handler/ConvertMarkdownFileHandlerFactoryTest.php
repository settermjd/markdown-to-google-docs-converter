<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\ConvertMarkdownFileHandler;
use App\Handler\ConvertMarkdownFileHandlerFactory;
use App\Service\MarkdownConverterService;
use Mezzio\Template\TemplateRendererInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

final class ConvertMarkdownFileHandlerFactoryTest extends TestCase
{
    private vfsStreamDirectory $root;

    public function setUp(): void
    {
        $this->root = vfsStream::setup("root", null, [
            'uploads' => []
        ]);
    }

    public function testCanEscapeCodeBlocksInUploadedMarkdown(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container
            ->expects($this->atMost(3))
            ->method('get')
            ->willReturnOnConsecutiveCalls(
                $this->createMock(TemplateRendererInterface::class),
                $this->createMock(MarkdownConverterService::class),
                [
                    'app' => [
                        'upload' => vfsStream::url('uploads'),
                        'convert' => '',
                        'styles' => '',
                    ]
                ]
            );

        $factory = new ConvertMarkdownFileHandlerFactory();
        $this->assertInstanceOf(ConvertMarkdownFileHandler::class, $factory($container));
    }
}
