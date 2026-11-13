<?php

declare(strict_types=1);

use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\File\FileCache;

/** @var array $params */

// yiisoft/cache binds the PSR-16 cache to ArrayCache by default. That one lives only for a single request,
// so every page view builds the wisdom index again: new and edited files show up at once (dev).
// With 'wisdom.persistentCache' (test, prod) a FileCache keeps the entries in runtime/cache across requests.
if (!($params['wisdom']['persistentCache'] ?? false)) {
    return [];
}

return [
    CacheInterface::class => static fn(Aliases $aliases): FileCache => new FileCache($aliases->get('@runtime/cache')),
];
