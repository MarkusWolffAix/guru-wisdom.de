<?php

declare(strict_types=1);

namespace App\Web\Imprint;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;

/**
 * Action for displaying the Imprint (Imprint) page.
 * Follows the Single Action Controller (ADR) pattern.
 */
final class Action implements RequestHandlerInterface
{
    /** @var WebViewRenderer */
    private WebViewRenderer $viewRenderer;

    /**
     * @param WebViewRenderer $viewRenderer
     */
    public function __construct(WebViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withViewPath('@views/imprint');
    }

    /**
     * Handles the request for the Imprint page.
     * 
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
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
