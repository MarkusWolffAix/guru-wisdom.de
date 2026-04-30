<?php

declare(strict_types=1);

namespace App\Web\Deployment;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

final class Action implements RequestHandlerInterface
{
    // NEU: WebViewRenderer
    private WebViewRenderer $viewRenderer;

    // NEU: WebViewRenderer im Konstruktor
    public function __construct(WebViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withViewPath('@views/deploy');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->viewRenderer->render('template');
    }
}