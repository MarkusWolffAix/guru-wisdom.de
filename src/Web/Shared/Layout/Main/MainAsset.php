<?php

declare(strict_types=1);

namespace App\Web\Shared\Layout\Main;

use Yiisoft\Assets\AssetBundle;

final class MainAsset extends AssetBundle
{
// 1. Wo landen die Dateien für den Browser? (Standard Yii-Aliase)
    public ?string $basePath = '@assets'; 
    public ?string $baseUrl = '@assetsUrl'; 

    // 2. Wo liegen deine Original-Dateien auf dem Server?
    // @root zeigt auf das Hauptverzeichnis (/app), und dort in den Ordner 'assets'
    public ?string $sourcePath = '@root/assets';

    public array $css = [
	    'css/guruwisdom.css',
    ];
}