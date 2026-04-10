<?php

declare(strict_types=1);

namespace App\Web\Contact;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class Action
{
    public function __construct(
        private ViewRenderer $viewRenderer
    ) {
        // Wir weisen den Renderer an, den 'Contact' Ordner für die Views zu nutzen
        $this->viewRenderer = $viewRenderer->withControllerName('Contact'); 
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Wir laden die template.php
        return $this->viewRenderer->render('template');
    }
}
