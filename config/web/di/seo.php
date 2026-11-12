<?php

declare(strict_types=1);

use App\Web\Sitemap\Action as SitemapAction;

/** @var array $params */

return [
    SitemapAction::class => [
        '__construct()' => [
            'params' => $params,
        ],
    ],
];
