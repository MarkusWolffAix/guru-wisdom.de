<?php
declare(strict_types=1);

/**
 * Für eine saubere IDE-Unterstützung (wie in PhpStorm) ist es guter Stil, 
 * die Variablen oben im Template einmal zu deklarieren:
 *
 * @var string $id
 * @var string $title
 * @var string $subtitle
 * @var string $wisdomText
 */
?>

<div class="wisdom-container">
    <h1><?= htmlspecialchars($title) ?></h1>

    <?php if ($subtitle !== ''): ?>
        <h3 class="text-muted"><?= htmlspecialchars($subtitle) ?></h3>
    <?php endif; ?>

    <hr>
                <?= $id ?>

    <div class="wisdom-content">
        <?= $wisdomText ?>
    </div>
</div>