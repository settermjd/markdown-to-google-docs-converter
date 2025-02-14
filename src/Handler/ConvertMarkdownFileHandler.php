<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\MarkdownConverterService;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Stream;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pandoc\Pandoc;

final class ConvertMarkdownFileHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly TemplateRendererInterface $view,
        private readonly MarkdownConverterService $converter,
        private array $config = []
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getMethod()) {
            return new HtmlResponse($this->view->render('convert-markdown', []));
        }

        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['file'];
        if (! $uploadedFile->getError() === UPLOAD_ERR_OK) {
            // ...
        }

        $filename = $this->config['upload'] . DIRECTORY_SEPARATOR . $uploadedFile->getClientFilename();
        $uploadedFile->moveTo($filename);
        $convertedFile = $this->converter
            ->convertMarkdown(
                file_get_contents($filename),
                pathinfo(basename($uploadedFile->getClientFilename()), PATHINFO_FILENAME) . '.odt'
            );

        $downloadFile = new Stream($convertedFile);
        return new Response($downloadFile)
            ->withAddedHeader('Pragma', 'public')
            ->withAddedHeader('Expires', 0)
            ->withAddedHeader(
                'Cache-Control', 
                [
                    'must-revalidate, post-check=0, pre-check=0',
                    'private'
                ]
            )
            ->withAddedHeader('Content-Type', 'application/vnd.oasis.opendocument.text')
            ->withAddedHeader('Content-Disposition', 'attachment; filename="download.odt"')
            ->withAddedHeader('Content-Transfer-Encoding', 'binary')
            ->withAddedHeader('Content-Length', $downloadFile->getSize());
    }
}
