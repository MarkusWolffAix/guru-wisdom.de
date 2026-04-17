<?php

declare(strict_types=1);

namespace App\Web\PrivacyPolicy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
/**
 * Action for displaying the Privacy Policy (Datenschutzerklärung) page.
 * Follows the Single Action Controller (ADR) pattern.
 */
final class Action implements RequestHandlerInterface
{
    // NEU: WebViewRenderer
    private WebViewRenderer $viewRenderer;

    // NEU: WebViewRenderer im Konstruktor
    public function __construct(WebViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withViewPath('@views/privacypolicy');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->viewRenderer->render('template');
    }
}