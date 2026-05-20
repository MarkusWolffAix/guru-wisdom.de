<?php

declare(strict_types=1);

use Yiisoft\Html\Html; 
use Yiisoft\View\WebView;

/**
 * For clean IDE support (like in PhpStorm), it is good practice 
 * to declare the available variables at the top of the template:
 *
 * @var string      $id             The unique identifier of the wisdom.
 * @var string      $title          The main title of the wisdom.
 * @var string      $subtitle       The subtitle (if available).
 * @var string      $wisdomText     The parsed HTML text/markdown content.
 * @var string      $image          The rendered HTML string for the image.
 * @var string      $audio          The rendered HTML string for the audio player.
 * @var string|null $description    A short description or excerpt.
 * @var string|null $prevId         The ID of the previous wisdom (for navigation).
 * @var string|null $nextId         The ID of the next wisdom (for navigation).
 * @var string      $currentUrl     The dynamic absolute URL from request.
 * @var string|null $datePublished  Publish date (if available, can be used for structured data and meta tags)
 * @var string|null $publisherName  Publisher name (if available, can be used for structured data and meta tags)
 * @var string|null $authorName     Author name (if available, can be used for structured data and meta tags)
 * @var WebView     $this           The view component rendering this template.
 */


$this->setTitle($title);
if (!empty($description)) {
    $this->registerMeta(['name' => 'description', 'content' => "$description"], 'description');
} else  {
    $this->registerMeta(['name' => 'description', 'content' => 'Entdecke tiefgründige Weisheiten und Zitate für jeden Tag. Lass dich inspirieren und finde neue Perspektiven für dein Leben.'], 'description');
}

if (!empty($keywords)) {
    $this->registerMeta(['name' => 'keywords', 'content' => "$keywords"], 'keywords');
} else {
    $this->registerMeta(['name' => 'keywords', 'content' => 'Weisheiten, Zitate, Inspiration, Philosophie, Spiritualität, Nordische Mythologie, Guru-Wisdom'], 'keywords');
}
$ogImageUrl = 'https://media.guru-wisdom.de/images/' . $id . '.jpg';
$finalPublisher = !empty($publisherName) ? $publisherName : 'GURU Wisdom';
$finalAuthor = 'Markus Wolff'; // Standard-Fallback, falls gar nichts angegeben ist
if (!empty($authorName)) {
    $authorWords = explode(' ', trim((string)$authorName));
    $finalAuthor = implode(' ', array_slice($authorWords, 0, 2));
}


$this->registerMeta(['property' => 'og:title', 'content' => $title], 'og:title');
$this->registerMeta(['property' => 'og:description', 'content' =>  $description ], 'og:description');
$this->registerMeta(['property' => 'og:image', 'content' => $ogImageUrl], 'og:image');
$this->registerMeta(['property' => 'og:url', 'content' => $currentUrl], 'og:url');
$this->registerMeta(['property' => 'og:type', 'content' => 'article'], 'og:type'); 


$this->registerMeta(['name' => 'twitter:card', 'content' => 'summary_large_image'], 'twitter:card');
$this->registerMeta(['name' => 'twitter:title', 'content' => $title], 'twitter:title');
$this->registerMeta(['name' => 'twitter:description', 'content' => $description], 'twitter:description');
$this->registerMeta(['name' => 'twitter:image', 'content' => $ogImageUrl], 'twitter:image');

$this->registerLinkTag(Html::link($currentUrl, ['rel' => 'canonical']));

$this->registerMeta(['name' => 'theme-color', 'content' => '#ffffff'], 'theme-color');

$schemaData = [
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $title,
    'description' => $description,
    'image' => [
        $ogImageUrl
    ],
    'author' => [
        '@type' => 'Person',
        'name' => $finalAuthor, 
        'url' => 'https://guru-wisdom.de/impressum' 
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => $finalPublisher, 
        'logo' => [
            '@type' => 'ImageObject',
            'url' => 'https://guru-wisdom.de/images/logo/GuruWisdom.jpg'
        ]
    ],
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $currentUrl
    ]
];

// Falls ein Datum im Markdown hinterlegt war, fügen wir es ISO 8601 formatiert hinzu
if (!empty($datePublished)) {
    $schemaData['datePublished'] = date('c', strtotime((string)$datePublished));
}

// Array in einen sicheren JSON-String umwandeln
$jsonLd = json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>

<script type="application/ld+json">
    <?= $jsonLd ?>
</script>


<div class="d-flex justify-content-center w-100">
    <div class="wisdom-card w-100">
        
        <div class="image-container">
            <?= $image ?>
        </div>
        
        <div class="action-bar">
            <?= Html::a(
                Html::img('/images/icons/ArrowLeft.jpg')
                    ->id('before-button')
                    ->alt('previous wisdom')
                    ->attribute('width', '24'),
                '/' . $prevId
            )->class('nav-btn')->attribute('aria-label', 'Previous wisdom') ?>

            <div class="audio-player">
                <?= $audio ?>
            </div>

            <button id="toggle-button" class="nav-btn" aria-label="Show details">
                <img id="lupe-icon" src="/images/icons/MagnifyingGlass.jpg" alt="show details" width="24">
            </button>

            <?= Html::a(
                Html::img('/images/icons/ArrowRight.jpg')
                    ->id('next-button')
                    ->alt('next wisdom')
                    ->attribute('width', '24'),
                '/' . $nextId
            )->class('nav-btn')->attribute('aria-label', 'Next wisdom') ?>
        </div>
        
        <div class="preview-container" id="previewContainer">
            <div class="preview-content">
                <div class="p-4 pb-2 text-center">
                    <h1 class="mb-2"><?= Html::encode($title) ?></h1>
                    
                    <?php if (!empty($subtitle)): ?>
                        <h2 class="text-muted mb-3" style="font-size: 1.2rem;">
                            <?= Html::encode($subtitle) ?>
                        </h2>
                    <?php endif; ?>
                    
                    <?php if (!empty($description)): ?>
                        <p style="font-size: 1.1rem; color: #555;">
                            <?= Html::encode($description) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="text-container" id="detailsContainer">
            <div class="text-content">
                <div id="markdown-body" class="p-4">
                    <?= $wisdomText ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$needsMathJax = str_contains($wisdomText, '$') || str_contains($wisdomText, '\[');

// Wenn ja, registrieren wir das CDN-Skript für diese Seite
if ($needsMathJax) {
// 1. External MathJax script to render LaTeX formulas within the markdown content.
// In Yii3, the position is passed as the second argument!
// 1. Zuerst deine lokale Konfigurationsdatei laden
// Der Pfad beginnt mit '/', was auf dein Web-Stammverzeichnis (public) verweist


// 1. Config direkt als String in den <head> schreiben
$mathJaxConfig = <<<'JS'
window.MathJax = {
  tex: {
    inlineMath: [['$', '$'], ['\\(', '\\)']],
    displayMath: [['$$', '$$'], ['\\[', '\\]']]
  }
};
JS;

$this->registerJs($mathJaxConfig, WebView::POSITION_HEAD);

// 2. CDN Skript laden
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js',
    WebView::POSITION_HEAD,

    [
        'async' => true,
        'id' => 'mathjax-script'
    ]
);


}; 


?>