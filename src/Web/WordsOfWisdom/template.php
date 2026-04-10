<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * @var WebView $this
 * @var string $id
 * @var UrlGeneratorInterface $urlGenerator
 */

$title = 'Words of Wisdom';

// Set page title for the browser tab and layout
$this->setTitle($title);

?>
<div class="container py-5 text-center">
    
    <h1 class="mb-5 display-4" style="font-family: 'Caveat', cursive; color: #4a4a4a;">
        GURU Wisdom
    </h1>

    <div class="card shadow mx-auto border-0 rounded-4" style="max-width: 800px; background-color: #fafafa;">
        <div class="card-body p-md-5 p-4">
            <blockquote class="blockquote mb-0">
                
                <p class="fs-3 mb-4" style="font-family: 'Lora', serif; line-height: 1.8; color: #333;">
                    "Dies ist der Platzhalter für deine Weisheit.<br>
                    Die aktuell geladene ID ist: <strong><?= Html::encode($id) ?></strong>."
                </p>
                
                <footer class="blockquote-footer text-muted mt-4">
                    Dein <cite title="Source Title">GURU Wisdom</cite>
                </footer>
            </blockquote>
        </div>
    </div>

    <div class="mt-5">
        <a href="<?= $urlGenerator->generate('wordsofwisdom.index') ?>" class="btn btn-outline-dark btn-lg px-5 rounded-pill shadow-sm">
            🎲 Neue Weisheit ziehen
        </a>
    </div>

</div>
