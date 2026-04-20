<?php

declare(strict_types=1);

namespace App\Web\WordsOfWisdom;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Router\CurrentRoute;
use Yiisoft\Yii\View\Renderer\WebViewRenderer;
use App\Helper\BaseGuruWisdom;

/** 

 * @var string|null $id
 * @var string $title
 * @var string $subtitle
 * @var string $wisdomText
 * @var string $image
 * @var string $audio
 * @var string|null $prevId
 * @var string|null $nextId
 * @var CurrentRoute $currentRoute
 * @var BaseGuruWisdom $guruWisdom 
 * @var WebViewRenderer $viewRenderer
 * 
*/

final class Action implements RequestHandlerInterface
{
    private WebViewRenderer $viewRenderer;

    public function __construct(
        WebViewRenderer $viewRenderer, 
        private CurrentRoute $currentRoute, 
        private BaseGuruWisdom $guruWisdom
    ) {
        // Set view path to current directory
        $this->viewRenderer = $viewRenderer->withViewPath(__DIR__);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $this->currentRoute->getArgument('id');
        $id= $this->guruWisdom->sanitizeId($id);
        $wisdomData = $this->guruWisdom->parseFile($id);
        $image = $this->guruWisdom->getImageHtml($id);
        $audio = $this->guruWisdom->getAudioHtml($id);
        $navids = $this->guruWisdom->getNavigationIds($id);
        $prevId = $navids['prev'] ?? null;
        $nextId = $navids['next'] ?? null;

        return $this->viewRenderer
        ->withLayout('@src/Web/Shared/Layout/Main/layout') // force Path tolayout
        ->render('template', [
            'id' => $id, 
            'wisdomText' => $wisdomData['htmloutput'] ?? '', 
            'title' => $wisdomData['title'] ?? 'Kein Titel',
            'subtitle' => $wisdomData['subtitle'] ?? '',
            'image' => $image, 
            'audio' => $audio,
            'prevId' => $prevId,
            'nextId' => $nextId
        ]);

    }
}