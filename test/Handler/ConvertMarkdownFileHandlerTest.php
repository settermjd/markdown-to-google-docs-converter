<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\ConvertMarkdownFileHandler;
use App\Service\MarkdownConverterService;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\UploadedFile;
use Mezzio\Template\TemplateRendererInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

final class ConvertMarkdownFileHandlerTest extends TestCase
{
    public const string PHYSICAL_UPLOAD_DIR = "/tmp/uploaded";
    public const string PHYSICAL_CONVERTED_DIR = "/tmp/converted";

    private vfsStreamDirectory $vfs;

    public function setUp(): void
    {
        $this->vfs = vfsStream::setup(sys_get_temp_dir(), null, [
            'uploads' => [],
            'converted' => [
                'README.original.odt' => file_get_contents(
                    realpath(__DIR__) . '/../_data/files/README.original.odt'
                )
            ]
        ]);
    }

    public function testRendersFormForGetRequests(): void
    {
        $template = $this->createMock(TemplateRendererInterface::class);
        $template
            ->expects($this->once())
            ->method('render')
            ->with('convert-markdown', [])
            ->willReturn('');
        $handler = new ConvertMarkdownFileHandler(
            $template,
            $this->createMock(MarkdownConverterService::class),
            [
                'upload' => vfsStream::url('uploads')
            ]
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('get');

        $response = $handler->handle($request);
        $this->assertSame('', (string) $response->getBody());
    }

    public function testCanConvertMarkdownFileWithoutImages(): void
    {
        $markdownConverter = $this->createMock(MarkdownConverterService::class);
        $markdownConverter
            ->expects($this->once())
            ->method('convertMarkdown')
            ->willReturn($this->vfs->url() . "/converted/README.original.odt");
        $handler = new ConvertMarkdownFileHandler(
            $this->createMock(TemplateRendererInterface::class),
            $markdownConverter,
            [
                'upload' => $this->vfs->url() . "/uploads",
            ]
        );

        $markdownFile = realpath(__DIR__) . '/../_data/files/README.original.md';
        $uploadedFile = new UploadedFile(
            new Stream($markdownFile),
            filesize($markdownFile),
            UPLOAD_ERR_OK,
            basename($markdownFile),
            mime_content_type($markdownFile)
        );

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getUploadedFiles')
            ->willReturn(
                [
                    'file' => $uploadedFile
                ]
            );

        $response = $handler->handle($request);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('public', $response->getHeaderLine('Pragma'));
        $this->assertSame('0', $response->getHeaderLine('Expires'));
        $this->assertSame(
            [
                'must-revalidate, post-check=0, pre-check=0',
                'private'
            ],
            $response->getHeader('Cache-Control')
        );
        $this->assertSame('application/vnd.oasis.opendocument.text', $response->getHeaderLine('Content-Type'));
        $this->assertSame('attachment; filename="download.odt"', $response->getHeaderLine('Content-Disposition'));
        $this->assertSame('binary', $response->getHeaderLine('Content-Transfer-Encoding'));
        $this->assertSame('18186', $response->getHeaderLine('Content-Length'));
        $this->assertNotEmpty($response->getBody()->getContents());
    }
}
