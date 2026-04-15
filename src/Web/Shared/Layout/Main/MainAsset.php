<?php

declare(strict_types=1);

namespace App\Web\Shared\Layout\Main;

use Yiisoft\Assets\AssetBundle;

final class MainAsset extends AssetBundle
{
    public ?string $sourcePath = '@root/assets';
    public ?string $basePath = '@assets'; 
    public ?string $baseUrl = '@assetsUrl'; 

public array $css = [
        'css/bootstrap.5.3.2.min.css', 
        'css/guruwisdom.css', 
    ];

    public array $js = [
        'js/bootstrap.bundle.5.3.2.min.js',
    ];
}