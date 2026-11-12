<?php

declare(strict_types=1);

namespace App\Web\Robots;

use App\Service\SitemapBuilder;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * GET /robots.txt - points to the sitemap of the requested domain; test and local hosts are closed for crawlers.
 */
final class Action implements RequestHandlerInterface
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri();
        $body = SitemapBuilder::robots(
            SitemapBuilder::baseUrl($uri->getHost(), $uri->getScheme(), $uri->getPort()),
            SitemapBuilder::crawlingAllowed($uri->getHost()),
        );

        $response = $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'text/plain; charset=utf-8')
            ->withHeader('Cache-Control', 'public, max-age=3600');
        $response->getBody()->write($body);

        return $response;
    }
}
