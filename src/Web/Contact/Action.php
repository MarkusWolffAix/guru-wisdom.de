<?php

declare(strict_types=1);

namespace App\Web\Contact;

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
        $this->viewRenderer = $viewRenderer->withViewPath('@views/contact');
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
