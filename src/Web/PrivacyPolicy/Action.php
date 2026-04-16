<?php

declare(strict_types=1);

namespace App\Web\PrivacyPolicy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class Action implements RequestHandlerInterface
{
    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        /**
         * We set the view path to the feature-specific folder.
         * The alias @views points to /app/resources/views.
         */
    $this->viewRenderer = $viewRenderer->withViewPath('@views/privacypolicy');

    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /**
         * Yii3's View Localization:
         * Because we call render('template'), and our file is named 'template.de.php',
         * Yii will automatically select the German version if the locale is set to 'de'.
         */

        return $this->viewRenderer->render('template');
    }
}