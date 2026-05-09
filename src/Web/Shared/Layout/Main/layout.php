<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var string $content
 * @var \Yiisoft\Assets\AssetManager $assetManager
 * @var \Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var \Yiisoft\User\CurrentUser $currentUser
 * @var \Psr\Http\Message\ServerRequestInterface $request
 * @var string $environment
 * @var string $language
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 */

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;
use Yiisoft\Bootstrap5\Nav;
use Yiisoft\Bootstrap5\NavLink;
use Yiisoft\Bootstrap5\NavBar;

$assetManager->register(MainAsset::class);
$lang = $translator->getLocale(); 

// 1. Dynamische Basis-URL für das Asset Bundle ermitteln
$mainAssetBundle = $assetManager->getBundle(MainAsset::class);
$baseUrl = $mainAssetBundle ? $mainAssetBundle->baseUrl : '';

// 2. Schriftarten vorab laden (Preload) - Verhindert "Render-Blocking"
if ($baseUrl !== '') {
    $this->registerLinkTag([
        'rel' => 'preload',
        'href' => $baseUrl . '/fonts/caveat-v7-latin_cyrillic-regular.woff2',
        'as' => 'font',
        'type' => 'font/woff2',
        'crossorigin' => 'anonymous'
    ]);

    $this->registerLinkTag([
        'rel' => 'preload',
        'href' => $baseUrl . '/fonts/Lora-Regular.woff2',
        'as' => 'font',
        'type' => 'font/woff2',
        'crossorigin' => 'anonymous'
    ]);
}

$this->addCssFiles($assetManager->getCssFiles());
$this->addJsFiles($assetManager->getJsFiles());

$this->registerMeta(['charset' => 'UTF-8'], 'charset');
$this->registerMeta(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no'], 'viewport');

$this->registerMeta(['name' => 'author', 'content' => 'Markus Wolff'], 'author');
$this->registerMeta(['name' => 'robots', 'content' => 'index, follow'], 'robots');

$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => '/favicon.ico']);

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($lang ?? 'de') ?>" class="h-100">
<head>
    <title><?= Html::encode($this->getTitle()) ?></title>    
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
<nav class="navbar navbar-light bg-white fixed-top border-bottom py-1">
    
    <a href="<?= $urlGenerator->generate('wordsofwisdom.index-' . $lang) ?>" class="navbar-brand ms-3 mt-1 py-0">
        
        <picture>
            <source srcset="/images/logo/GuruWisdom.webp" type="image/webp">
            <img src="/images/logo/GuruWisdom.jpg" alt="Guru Wisdom" id="brand-logo" style="height: 40px; width: auto;" width="120" height="40">
        </picture>
        
    </a>
</nav>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php /* Falls Breadcrumbs benötigt werden: echo Breadcrumbs::widget()->links($this->getParameter('breadcrumbs', [])); */ ?>
        <?= '' /* Alert::widget() - Bitte prüfen, ob das Alert-Widget für Yii3 portiert wurde */ ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3 bg-white text-white">
    <div class="container">
        <div class="row text-muted">
    
        <div class="container text-center">
        <div class="mb-3">
           
            <a href="https://www.instagram.com/guru2wisdom" class="me-3" >
                <img src="/images/icons/instagram.png" alt="Instagram" width="20" height="20" loading="lazy">
            </a>
            <a href="https://www.facebook.com/profile.php?id=61577589013906" class="me-3">
                <img src="/images/icons/facebook.png" alt="Facebook" width="20" height="20" loading="lazy">
            </a>
            <a href="https://www.youtube.com/@guru2wisdom" class="me-3">
                <img src="/images/icons/youtube.png" alt="Youtube" width="20" height="20" loading="lazy">
            </a>
        </div>
        
        <div class="small text-muted mb-2">
            <a href="<?= $urlGenerator->generate('contact-' . $lang) ?>" class="text-decoration-none text-muted mx-2">
                <?= $translator->translate('menu.contact', [], 'app') ?>
            </a> |
    
            <a href="<?= $urlGenerator->generate('impressum-' . $lang) ?>" class="text-decoration-none text-muted mx-2">
              <?= $translator->translate('menu.imprint', [], 'app') ?>
            </a> |
    
            <a href="<?= $urlGenerator->generate('privacypolicy-' . $lang) ?>" class="text-decoration-none text-muted mx-2">
                 <?= $translator->translate('menu.privacy', [], 'app') ?>
            </a>
        </div>

        <div class="text-muted">
            <small>&copy; GURU Wisdom <?= date('Y') ?></small>
        </div>
       <div> 
    </div>
</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>