<?php

declare(strict_types=1);

use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\File\FileCache;

// yiisoft/cache binds the PSR-16 cache to ArrayCache by default. That one lives only for a single
// request, so nothing was ever reused. FileCache keeps the entries in runtime/cache across requests.
return [
    CacheInterface::class => static fn(Aliases $aliases): FileCache => new FileCache($aliases->get('@runtime/cache')),
];
