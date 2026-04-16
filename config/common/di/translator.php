<?php

declare(strict_types=1);

use Yiisoft\Aliases\Aliases;
use Yiisoft\Translator\CategorySource;
use Yiisoft\Translator\Message\Php\MessageSource;

return [
    // The source of translations for the "app" category
    'translation.app' => [
        'definition' => static function (Aliases $aliases) {
            // lookup the path to the messages directory using the alias system
            $pfad = $aliases->get('@root/resources/messages');
            $reader = new MessageSource($pfad);
            
            return new CategorySource('app', $reader);
        },
        // This tag is magic: Yii3 automatically collects all services with this tag
        'tags' => ['translation.categorySource'],
    ],
];