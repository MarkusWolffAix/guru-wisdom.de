<?php

declare(strict_types=1);

namespace App\Web\PrivacyPolicy;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\Yii\View\Renderer\ViewRenderer;

final class Action
{
    public function __construct(
        private ViewRenderer $viewRenderer
    ) {
        // Wir setzen den Kontext auf den 'PrivacyPolicy' Ordner
        $this->viewRenderer = $viewRenderer->withControllerName('PrivacyPolicy'); 
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Rendern der template.php
        return $this->viewRenderer->render('template');
    }
}
