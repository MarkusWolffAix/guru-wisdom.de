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
 * @var string|null $prevId
 * @var string|null $nextId
*/
?>

<div class="wisdom-container">
<?php

$this->setTitle($title);

echo "<h1>".$title."</h1>";
if(!empty($subtitle)){
    echo "<h2>".$subtitle."</h2>";
}

echo $image."<br/>"; 
echo $audio;
?>
<div class="wisdom-navigation">
    <?= Html::a(
        Html::img('/images/icons/ArrowLeft.png')
            ->id('before-button')
            ->class('wisdom-nav-icon')
            ->alt('before wisdom'),
        '/' . $prevId
    ) ?>
    <?= Html::a(
        Html::img('/images/icons/MagnifyingGlass.png')
            ->id('toggle-button')
            ->class('wisdom-nav-icon')
            ->alt('show details'),
    ) ?>
        <?= Html::a(
        Html::img('/images/icons/ArrowRight.png')
            ->id('next-button')
            ->class('wisdom-nav-icon')
            ->alt('next wisdom'),
        '/' . $nextId
    ) ?>
</div>

<!-- Das DIV-Element, das versteckt/gezeigt werden soll -->
<div id="markdown-body" style="display:none;">
<?php 
      echo $wisdomText;
      // echo $wisdom['htmloutput'] ?? 'Der Inhalt dieser Weisheit konnte leider nicht geladen werden.';
      // echo GuruWisdom::getWisdomContent($id)    ;
?>
</div>

</div>


<?php
// 1. extern MathJax Script to show LaTeX formulas in the markdown content
// In Yii3 wird die Position als 2. Argument übergeben, nicht mehr in einem Array!
$this->registerJsFile(
    'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js',
    WebView::POSITION_HEAD
);
?>
<!-- 2. Vanilla JS Script to toggle the visibility of the markdown content -->

<?php


// 3. Vanilla JS YouTube-Script
/*
$jsYoutube = <<<JS
    document.querySelectorAll('.youtube-placeholder').forEach(function(placeholder) {
        placeholder.addEventListener('click', function() {
            var videoId = this.getAttribute('data-video-id');
            var iframe = '<iframe src="https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="border-radius: 8px;"></iframe>';
            this.innerHTML = '<div class="ratio ratio-16x9">' + iframe + '</div>';
        });
    });
JS;
// POSITION_END places the script just before the closing </body> tag, which is ideal for scripts that manipulate the DOM after it has loaded.
$this->registerJs($jsYoutube, WebView::POSITION_END);
*/

?>