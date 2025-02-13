<?php

declare(strict_types=1);

namespace App\Handler;

use App\Handler\ConvertMarkdownFileHandler;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

final class ConvertMarkdownFileHandlerFactory
{
    public function __invoke(ContainerInterface $container): ConvertMarkdownFileHandler
    {
        return new ConvertMarkdownFileHandler(
            $container->get(TemplateRendererInterface::class)
        );
    }
}
