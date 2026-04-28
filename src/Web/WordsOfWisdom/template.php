<?php

declare(strict_types=1);

use Yiisoft\Html\Html; 
use Yiisoft\View\WebView;

/**
 * For clean IDE support (like in PhpStorm), it is good practice 
 * to declare the available variables at the top of the template:
 *
 * @var string      $id          The unique identifier of the wisdom.
 * @var string      $title       The main title of the wisdom.
 * @var string      $subtitle    The subtitle (if available).
 * @var string      $wisdomText  The parsed HTML text/markdown content.
 * @var string      $image       The rendered HTML string for the image.
 * @var string      $audio       The rendered HTML string for the audio player.
 * @var string|null $description A short description or excerpt.
 * @var string|null $prevId      The ID of the previous wisdom (for navigation).
 * @var string|null $nextId      The ID of the next wisdom (for navigation).
 * @var WebView     $this        The view component rendering this template.
 */

$this->setTitle($title);
if (!empty($description)) {
    $this->setParameter('meta_description', $description);
}
if (!empty($keywords)) {
    $this->setParameter('meta_keywords', $keywords);
}
?>

<div class="d-flex justify-content-center w-100">
    <div class="wisdom-card w-100">
        
        <div class="image-container">
            <?= $image ?>
        </div>
        
        <div class="action-bar">
            <?= Html::a(
                Html::img('/images/icons/ArrowLeft.png')
                    ->id('before-button')
                    ->alt('previous wisdom')
                    ->attribute('width', '24'),
                '/' . $prevId
            )->class('nav-btn')->attribute('aria-label', 'Previous wisdom') ?>

            <div class="audio-player">
                <?= $audio ?>
            </div>

            <button id="toggle-button" class="nav-btn" aria-label="Show details">
                <img id="lupe-icon" src="/images/icons/MagnifyingGlass.png" alt="show details" width="24">
            </button>

            <?= Html::a(
                Html::img('/images/icons/ArrowRight.png')
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
// 1. External MathJax script to render LaTeX formulas within the markdown content.
// In Yii3, the position is passed as the second argument!
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js',
    WebView::POSITION_HEAD
);
?>