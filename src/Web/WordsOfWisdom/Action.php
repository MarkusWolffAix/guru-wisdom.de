<?php

declare(strict_types=1);

namespace App\Web\WordsOfWisdom;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\ViewRenderer; 
use App\Helper\BaseGuruWisdom; // ✅ Den richtigen Helper importiert!

final class Action implements RequestHandlerInterface
{
    private ViewRenderer $viewRenderer;

    // ✅ PHP 8 Constructor Promotion: 'private' direkt in den Klammern spart Code!
    public function __construct(
        ViewRenderer $viewRenderer, 
        private CurrentRoute $currentRoute, 
        private BaseGuruWisdom $guruWisdom
    ) {
        // Set view path to current directory
        $this->viewRenderer = $viewRenderer->withViewPath(__DIR__);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get the 'id' argument. If the route doesn't have an '{id}', this returns null.
        $id = $this->currentRoute->getArgument('id');

        // Check if we are on the index route (no specific ID provided)
        if ($id === null) {
            // Hier könntest du künftig deine Logik für einen zufälligen Post einbauen
            $id = 'random'; 
        }
        
        // parseFile gibt jetzt ein Array zurück!
        $wisdomData = $this->guruWisdom->parseFile($id);

        return $this->viewRenderer->render('template', [
            'id' => $id,
            // Wir greifen auf den key 'htmloutput' des Arrays zu
            // Der Null-Coalescing Operator (?? '') verhindert Fehler, falls die Datei leer war
            'wisdomText' => $wisdomData['htmloutput'] ?? '', 
            // Bonus: Du kannst jetzt auch Title & Subtitle an den View übergeben!
            'title' => $wisdomData['title'] ?? 'Kein Titel',
            'subtitle' => $wisdomData['subtitle'] ?? ''
        ]);
    }
}