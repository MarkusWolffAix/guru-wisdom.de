<?php

declare(strict_types=1);

namespace App\Web\WordsOfWisdom;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use App\Service\GuruWisdomService;
use App\Service\WisdomCacheService;

/**
 * Handles the web requests for the "Words of Wisdom" detail page.
 * * This action loads the data for a specific wisdom (including text, 
 * image, audio, and navigation) and renders it using the corresponding view template.
 */
final class Action implements RequestHandlerInterface
{
    /**
     * @var WebViewRenderer The renderer for the view templates.
     */
    private WebViewRenderer $viewRenderer;

    /**
     * Initializes the Action class.
     *
     * @param WebViewRenderer $viewRenderer Component for rendering the HTML output.
     * @param CurrentRoute    $currentRoute Represents the currently matched route and its parameters.
     * @param GuruWisdomService  $guruWisdom   Helper class for accessing the parsed wisdom data.
     * @param WisdomCacheService $wisdomCache  Service für die performante Navigation.
     */
    public function __construct(
        WebViewRenderer $viewRenderer, 
        private CurrentRoute $currentRoute, 
        private GuruWisdomService $guruWisdom,
        private WisdomCacheService $wisdomCache // Hier injizieren wir das neue Gedächtnis
    ) {
        // Set the view path to the current directory of this class
        $this->viewRenderer = $viewRenderer->withViewPath(__DIR__);
    }

    /**
     * Processes the incoming server request and returns an HTTP response.
     *
     * Extracts the ID from the current route, sanitizes it, and loads the 
     * corresponding media and text data. These parameters are then passed 
     * to the template and rendered.
     *
     * @param ServerRequestInterface $request The incoming HTTP request.
     * @return ResponseInterface The rendered HTML response including the layout.
     */

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var string|null $id */
        $id = $this->currentRoute->getArgument('id');

        // 1. ID bereinigen, falls sie übergeben wurde
        if ($id !== null) {
            $id = $this->guruWisdom->sanitizeId($id);
        }

        // 2. Validierung: Prüfen, ob die ID im Archiv überhaupt existiert
        $isValid = false;
        if ($id !== null) {
            // Wir rufen alle bekannten Slugs blitzschnell aus dem Cache ab
            $allWisdoms = $this->wisdomCache->getSortedWisdoms();
            foreach ($allWisdoms as $wisdom) {
                if ($wisdom['slug'] === $id) {
                    $isValid = true;
                    break;
                }
            }
        }

        // 3. Der Fallback: Wenn keine ID da ist (Startseite) ODER die ID ungültig ist
        if ($id === null || !$isValid) {
            $latestWisdom = $this->wisdomCache->getLatestWisdom();
            // Ein kleiner Sicherheitsanker, falls das Archiv komplett leer sein sollte
            if ($latestWisdom === null) {
                throw new \RuntimeException("Das Archiv ist noch leer. Es gibt keine Weisheiten zum Anzeigen.");
            }
            
            // Wir überschreiben die (fehlende/falsche) ID mit der neuesten ID
            $id = $latestWisdom['slug'];
        }

        // 4. Ab hier ist absolut garantiert, dass $id ein gültiger String ist.
        // Das Parsing wird also niemals mehr mit "null" aufgerufen und wirft keinen Fehler.
        $wisdomData = $this->guruWisdom->parseFile($id);
        $image      = $this->guruWisdom->getImageHtml($id);
        $audio      = $this->guruWisdom->getAudioHtml($id);
        
        // Navigation (Neuere / Ältere Weisheit)
        $neighbors = $this->wisdomCache->getNeighbors($id);
        $newerWisdom = $neighbors['newer'];
        $olderWisdom = $neighbors['older'];

        // Metadaten und Tags bereinigen
        $tags = $wisdomData['tags'] ?? [];
        $categories = $wisdomData['categories'] ?? [];
        
        $cleanTags = array_map(function ($tag) {
            return trim(str_replace(['"', "'", '&quot;'], '', $tag));
        }, (array)$tags);

        $cleanCategories = array_map(function ($cat) {
            $cleanCat = trim(str_replace(['"', "'", '&quot;'], '', $cat));
            return 'Category ' . $cleanCat; 
        }, (array)$categories);
        
        $keywordsArray = array_unique(array_merge($cleanTags, $cleanCategories));
        $keywords = implode(', ', $keywordsArray);

        return $this->viewRenderer
            ->withLayout('@src/Web/Shared/Layout/Main/layout') // Force path to layout
            ->render('template', [
                'id'          => $id, 
                'wisdomText'  => $wisdomData['htmloutput'] ?? '', 
                'title'       => $wisdomData['title'] ?? 'No Title',
                'subtitle'    => $wisdomData['subtitle'] ?? '',
                'description' => $wisdomData['description'] ?? '',
                'keywords'    => $keywords,
                'image'       => $image, 
                'audio'       => $audio,
                // Wir übergeben nun die kompletten Arrays (oder null), 
                // damit das Template auch Titel/Datum für die Pfeile anzeigen kann
                'prevId' => $newerWisdom['slug'] ?? null,
                'nextId' => $olderWisdom['slug'] ?? null,
            ]); 
    }
}