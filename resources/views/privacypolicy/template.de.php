<?php
/** @var \Yiisoft\Translator\TranslatorInterface $translator */
?>

<div class="container py-5">
    <h1><?= $translator->translate('title', [], 'privacypolicy') ?></h1>
    <p><?= $translator->translate('intro', [], 'privacypolicy') ?></p>

    <h2 class="mt-4"><?= $translator->translate('responsible.title', [], 'privacypolicy') ?></h2>
    <p>
        <?= $translator->translate('responsible.text', [], 'privacypolicy') ?><br>
        <strong><?= $translator->translate('responsible.name', [], 'privacypolicy') ?></strong><br>
        <?= $translator->translate('responsible.contact', [], 'privacypolicy') ?>
    </p>

    <h2 class="mt-4"><?= $translator->translate('rights.title', [], 'privacypolicy') ?></h2>
    <p><?= $translator->translate('rights.text', [], 'privacypolicy') ?></p>
</div>