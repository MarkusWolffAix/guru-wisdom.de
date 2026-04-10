<?php

declare(strict_types=1);

namespace App\Web\Impressum;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class Action implements RequestHandlerInterface
{
    private ViewRenderer $viewRenderer;

    public function __construct(ViewRenderer $viewRenderer)
    {
        // Wir weisen den Renderer an, im aktuellen Ordner (__DIR__) nach den HTML-Dateien zu suchen
        $this->viewRenderer = $viewRenderer->withViewPath(__DIR__);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Rendert die Datei "template.php" aus demselben Verzeichnis
        return $this->viewRenderer->render('template');
    }
}
