<?php

declare(strict_types=1);

use App\Handler\ConvertMarkdownFileHandlerFactory;
use App\Handler\ConvertMarkdownFileHandler;
use Laminas\ServiceManager\ServiceManager;
use Asgrim\MiniMezzio\AppFactory;
use Mezzio\Router\FastRouteRouter;
use Mezzio\Router\Middleware\DispatchMiddleware;
use Mezzio\Router\Middleware\RouteMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Mezzio\Twig\TwigEnvironmentFactory;
use Mezzio\Twig\TwigExtension;
use Mezzio\Twig\TwigExtensionFactory;
use Mezzio\Twig\TwigRenderer;
use Mezzio\Twig\TwigRendererFactory;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/../vendor/autoload.php';

$container = new ServiceManager([
    'aliases' => [
        TemplateRendererInterface::class => TwigRenderer::class,
        'Twig_Environment'               => Environment::class,
    ],
    'factories' => [
        ConvertMarkdownFileHandler::class => ConvertMarkdownFileHandlerFactory::class,
        Environment::class   => TwigEnvironmentFactory::class,
        TwigExtension::class => TwigExtensionFactory::class,
        TwigRenderer::class  => TwigRendererFactory::class,
    ],
    'services' => [
        'config' => [
            'templates' => [
                'extension' => 'html.twig',
                'paths'     => [
                    'app'    => [realpath(__DIR__) . '/../data/templates/app'],
                    'error'  => [realpath(__DIR__) . '/../data/templates/error'],
                    'layout' => [realpath(__DIR__) . '/../data/templates/layout'],
                    FilesystemLoader::MAIN_NAMESPACE => [realpath(__DIR__) . '/../data/templates/app'],
                ],
            ],
            'twig' => [],
            'debug' => true,
        ]
    ]
]);
$router = new FastRouteRouter();
$app = AppFactory::create($container, $router);
$app->pipe(new RouteMiddleware($router));
$app->pipe(new DispatchMiddleware());
$app->get('/hello-world', new class implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return new TextResponse('Hello world!');
    }
});
$app->run();
