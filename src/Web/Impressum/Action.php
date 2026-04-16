<?php

declare(strict_types=1);

namespace App\Web\Impressum;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

/**
 * Action for displaying the Imprint (Impressum) page.
 * Follows the Single Action Controller (ADR) pattern.
 */
final class Action implements RequestHandlerInterface
{
    private ViewRenderer $viewRenderer;

    /**
     * @param ViewRenderer $viewRenderer The view renderer instance.
     * * We use withViewPath() to create a new instance of the renderer 
     * specifically pointed at the impressum view folder.
     */
    public function __construct(ViewRenderer $viewRenderer)
    {
        $this->viewRenderer = $viewRenderer->withViewPath('@views/impressum');
    }

    /**
     * Handles the incoming request and renders the imprint template.
     * * @param ServerRequestInterface $request The current HTTP request.
     * @return ResponseInterface The rendered HTML response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Renders the 'template.php' file located in 'resources/views/impressum/'
        return $this->viewRenderer->render('template');
    }
}