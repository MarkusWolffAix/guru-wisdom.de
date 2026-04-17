<?php

declare(strict_types=1);

namespace App\Web\Impressum;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Action for displaying the Imprint (Impressum) page.
 * Follows the Single Action Controller (ADR) pattern.
 */
final class Action implements RequestHandlerInterface
{
    private WebViewRenderer $viewRenderer;

    public function __construct(WebViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withViewPath('@views/impressum');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->viewRenderer->render('template');
    }
}