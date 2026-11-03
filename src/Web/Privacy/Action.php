<?php

declare(strict_types=1);

namespace App\Web\Privacy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
/**
 * Action for displaying the Privacy (Datenschutzerklärung) page.
 * Follows the Single Action Controller (ADR) pattern.
 */
final class Action implements RequestHandlerInterface
{
    // NEU: WebViewRenderer
    private WebViewRenderer $viewRenderer;

    // NEU: WebViewRenderer im Konstruktor
    public function __construct(WebViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withViewPath('@views/privacy');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var string $locale */
        $locale = $request->getAttribute('current_locale', 'de');
        $viewName = ($locale === 'en') ? 'en/template' : 'template';

        return $this->viewRenderer
          ->withLocale($locale)
          ->render($viewName);
    }
}
