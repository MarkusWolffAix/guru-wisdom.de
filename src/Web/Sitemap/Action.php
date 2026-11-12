<?php

declare(strict_types=1);

namespace App\Web\Sitemap;

use App\Service\SitemapBuilder;
use App\Service\WisdomCacheService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * GET /sitemap.xml - one sitemap per domain, built from the published wisdoms of that domain's language.
 */
final class Action implements RequestHandlerInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly WisdomCacheService $wisdomCache,
        private readonly array $params,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();
        $locale = (string) $request->getAttribute('current_locale', 'de');
        $switchMap = $this->params['multilingual']['domain_switch_map'] ?? [];

        $baseUrl = SitemapBuilder::baseUrl($uri->getHost(), $uri->getScheme(), $uri->getPort());
        $partnerBaseUrl = SitemapBuilder::partnerBaseUrl($switchMap, $uri->getHost(), $uri->getScheme(), $uri->getPort());
        $partnerWisdoms = $partnerBaseUrl === null
            ? []
            : $this->wisdomCache->getSortedWisdoms($locale === 'de' ? 'en' : 'de');

        $xml = SitemapBuilder::sitemap(
            $this->wisdomCache->getSortedWisdoms($locale),
            $baseUrl,
            $locale,
            $partnerBaseUrl,
            $partnerWisdoms,
        );

        $response = $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/xml; charset=utf-8')
            ->withHeader('Cache-Control', 'public, max-age=3600');
        $response->getBody()->write($xml);

        return $response;
    }
}
