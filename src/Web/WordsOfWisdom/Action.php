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
 * This action loads the data for a specific wisdom (including text, 
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
        private WisdomCacheService $wisdomCache
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

        // 1. STARTSEITE: Wenn KEINE ID übergeben wurde, zeigen wir die Übersicht (Schriftrolle)
        if ($id === null) {
            $allWisdoms = $this->wisdomCache->getSortedWisdoms();
            
            return $this->viewRenderer
                ->withLayout('@src/Web/Shared/Layout/Main/layout') // Force path to layout
                ->render('overview', [
                    'wisdoms' => $allWisdoms,
                ]); 
        }

        // 2. DETAILSEITE: Wenn eine ID da ist -> Validierung! (Ist sie im Archiv?)
        $isValid = false;
        $allWisdoms = $this->wisdomCache->getSortedWisdoms();
        foreach ($allWisdoms as $wisdom) {
            if ($wisdom['slug'] === $id) {
                $isValid = true;
                break;
            }
        }

        // 3. FALLBACK: Wenn die übergebene ID ungültig ist (z.B. Tippfehler in der URL)
        if (!$isValid) {
            $latestWisdom = $this->wisdomCache->getLatestWisdom();
            
            // Ein kleiner Sicherheitsanker, falls das Archiv komplett leer sein sollte
            if ($latestWisdom === null) {
                throw new \RuntimeException("Das Archiv ist noch leer. Es gibt keine Weisheiten zum Anzeigen.");
            }
            
            // Wir überschreiben die falsche ID mit der neuesten ID
            $id = $latestWisdom['slug'];
        }

        // 4. DATEN LADEN: Ab hier ist absolut garantiert, dass $id ein gültiger String ist.
        $wisdomData = $this->guruWisdom->parseFile($id);
        $image      = $this->guruWisdom->getImageHtml($id);
        $audio      = $this->guruWisdom->getAudioHtml($id);
        
        // Navigation (Neuere / Ältere Weisheit für die Pfeile)
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

        // 5. RENDERN DER EINZELNEN WEISHEIT
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
                'prevId'      => $newerWisdom['slug'] ?? null,
                'nextId'      => $olderWisdom['slug'] ?? null,
            ]); 
    }
}   