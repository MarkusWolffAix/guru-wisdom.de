<?php

declare(strict_types=1);

namespace App\Web\Login;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class Action
{
    public function __construct(
        private ViewRenderer $viewRenderer
    ) {
        // Wir weisen den Renderer an, in diesem Fall den 'Login' Ordner zu nutzen
        $this->viewRenderer = $viewRenderer->withControllerName('Login'); 
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Wir laden die template.php
        return $this->viewRenderer->render('template');
    }
}
