<?php

declare(strict_types=1);

use App\Console;

return [
    'hello' => Console\HelloCommand::class,
    'wisdom/clear-cache' => Console\ClearWisdomCacheCommand::class,
];
