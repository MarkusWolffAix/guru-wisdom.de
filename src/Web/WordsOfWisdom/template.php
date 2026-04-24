<?php
declare(strict_types=1);

use Yiisoft\Html\Html; 
use Yiisoft\View\WebView;

/**
 * Für eine saubere IDE-Unterstützung (wie in PhpStorm) ist es guter Stil, 
 * die Variablen oben im Template einmal zu deklarieren:
 *
 * @var string $id
 * @var string $title
 * @var string $subtitle
 * @var string $wisdomText
 * @var string $image
 * @var string $audio
 * @var string|null $description
 * @var string|null $prevId
 * @var string|null $nextId
*/

$this->setTitle($title);
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
                    ->alt('before wisdom')
                    ->attribute('width', '24'),
                '/' . $prevId
            )->class('nav-btn')->attribute('aria-label', 'Vorherige Weisheit') ?>

            <div class="audio-player">
                <?= $audio ?>
            </div>

            <button id="toggle-button" class="nav-btn" aria-label="Details anzeigen">
                <img id="lupe-icon" src="/images/icons/MagnifyingGlass.png" alt="show details" width="24">
            </button>

            <?= Html::a(
                Html::img('/images/icons/ArrowRight.png')
                    ->id('next-button')
                    ->alt('next wisdom')
                    ->attribute('width', '24'),
                '/' . $nextId
            )->class('nav-btn')->attribute('aria-label', 'Nächste Weisheit') ?>
            
        </div>
        <div class="preview-container" id="previewContainer">
            <div class="preview-content">
                <div class="p-4 pb-2 text-center">
                    <h1 class="mb-2"><?= Html::encode($title) ?></h1>
                    <?php if(!empty($subtitle)): ?>
                        <h2 class="text-muted mb-3" style="font-size: 1.2rem;"><?= Html::encode($subtitle) ?></h2>
                    <?php endif; ?>
                    
                    <?php if(!empty($description)): ?>
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
// 1. extern MathJax Script to show LaTeX formulas in the markdown content
// In Yii3 wird die Position als 2. Argument übergeben!
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js',
    WebView::POSITION_HEAD
);
?>