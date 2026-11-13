<?php

declare(strict_types=1);

return [
    'multilingual' => [
        'enable_lang_support_button' => true,
    ],
    // Keeps the wisdom index across requests (see config/common/di/cache.php). Dev does without.
    'wisdom' => [
        'persistentCache' => true,
    ],
];
