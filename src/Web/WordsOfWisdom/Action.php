<?php

declare(strict_types=1);

namespace App\Web\WordsOfWisdom;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\ViewRenderer;
// use App\Helper\GuruWisdom; // <-- Include your helper here!

final class Action implements RequestHandlerInterface
{
    private ViewRenderer $viewRenderer;
    private CurrentRoute $currentRoute;

    public function __construct(ViewRenderer $viewRenderer, CurrentRoute $currentRoute)
    {
        // Set view path to current directory
        $this->viewRenderer = $viewRenderer->withViewPath(__DIR__);
        $this->currentRoute = $currentRoute;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get the 'id' argument. If the route doesn't have an '{id}', this returns null.
        $id = $this->currentRoute->getArgument('id');

        // Check if we are on the index route (no specific ID provided)
        if ($id === null) {
            // Generate a random ID or fetch a random wisdom using your helper
            // Example: $wisdom = GuruWisdom::getRandom();
            $id = 'random'; // Placeholder: Replace this with your actual random logic
        }

	$wisdomText = GuruWisdom::getTextById($id); // (Beispielaufruf)

	return $this->viewRenderer->render('template', [
    		'id' => $id,
    		'wisdomText' => $wisdomText // <-- Hier übergibst du den fertigen Text
	]);
        // Render the shared template.php and pass the ID (or wisdom object)
        /* return $this->viewRenderer->render('template', [
            'id' => $id,
        ]);*/
    }
}
