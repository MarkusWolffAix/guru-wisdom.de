<?php

declare(strict_types=1);

/** @var \Yiisoft\View\WebView $this */
/** @var array $wisdoms */

$this->setTitle('Übersicht der Weisheiten');
?>

<!-- Hero Sektion: Das Auge -->
<header class="py-5 text-center position-relative">
    <div class="container">
        <div class="eye-container mx-auto" style="max-width: 600px;">
            <img src="/images/icons/gurueye.webp" alt="Das wachende Auge der Erkenntnis" class="img-fluid">
        </div>
        <h1 class="mt-2 fw-light">Guru Wisdom</h1>
        <p class="lead text-muted">Tritt ein und finde, was du suchst.</p>
    </div>
</header>       



<!-- Die Schriftrolle: Der magische Container -->
<section class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <!-- Der Schriftrollen-Hintergrund (Transparent durch CSS gesteuert) -->
            <div class="mystic-scroll-bg p-2 shadow-lg">
                
                <!-- Mystischer Hoch-Button (Gradient-Stil) -->
                <button id="scrollUpBtn" class="mystic-scroll-bar w-100 mb-2" aria-label="Zurück in die Vergangenheit">
                    <svg viewBox="0 0 24 24" width="40" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M 4 16 Q 12 4 20 16"></path>
                    </svg>
                </button>

                <!-- Scroll-Container: Zeigt durch CSS max-height nur 3 Karten gleichzeitig -->
                <div class="wisdom-scroll-area" id="wisdomContainer">
                    <?php 
                    $Count = count($wisdoms);
                    foreach ($wisdoms as $wisdom): ?>
                        <?php 
    
                            $id = $wisdom['id'] ?? $wisdom['slug'];
                            $title = $wisdom['title'] ?? 'Unbekannte Weisheit';
                            $title = "#" . $Count-- . '. ' . $title; // Nummerierung hinzufügen
                            $category = $wisdom['category'] ?? 'allgemein';
                            $description = $wisdom['description'] ?? '';
                            $tags = $wisdom['tags'] ?? [];
                            $tags = array_map(fn($item) => trim($item, '"'), $tags);
                         
                            $imagePathWebp = "https://media.guru-wisdom.de/images/thumb/{$id}.webp";
                            $imagePathJpg = "https://media.guru-wisdom.de/images/{$id}.jpg";
                        ?>

                        <a href="https://guru-wisdom.de/<?= htmlspecialchars((string)$id) ?>" 
                           class="text-decoration-none wisdom-link-wrapper mb-2">
                            <div class="card wisdom-card flex-row align-items-center p-3 shadow-sm border-0" 
                                 data-category="<?= htmlspecialchars((string)$category) ?>">
                                
                                <!-- Vorschaubild (90px Wächter-Container) -->
                                <div class="wisdom-thumb-container me-4">
                                    <picture class="wisdom-picture">
                                        <source srcset="<?= $imagePathWebp ?>" type="image/webp">
                                        <img src="<?= $imagePathJpg ?>" 
                                             alt="<?= htmlspecialchars((string)$title) ?>" 
                                             class="wisdom-thumb-img">
                                    </picture>
                                </div>
                                
                                <!-- Text-Inhalt -->
                                <div class="card-body p-0">
                                    <h3 class="card-title h5 fw-bold mb-2 text-dark">
                                        <?= htmlspecialchars((string)$title) ?>
                                    </h3>
                                    <p class="card-text text-muted mb-2 small">
                                        <?= htmlspecialchars((string)$description) ?>
                                    </p>
                                    <div class="tags">
                                        <?php foreach ($tags as $tag): ?>
                                            <span class="badge bg-light text-dark border-0 shadow-sm" style="font-size: 0.75rem;">
                                                <?= htmlspecialchars((string)$tag) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Mystischer Runter-Button (Gradient-Stil) -->
                <button id="scrollDownBtn" class="mystic-scroll-bar w-100 mt-2" aria-label="Weiter in die Zukunft">
                    <svg viewBox="0 0 24 24" width="40" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M 4 8 Q 12 20 20 8"></path>
                    </svg>
                </button>

            </div>
        </div>
    </div>
</section>

<!-- Offcanvas für Tags (wird durch den Button oben ausgelöst) -->
<div class="offcanvas offcanvas-bottom" tabindex="-1" id="tagOffcanvas" aria-labelledby="tagOffcanvasLabel" style="height: 50vh;">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="tagOffcanvasLabel">Tiefer filtern (Tags)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div id="tagCloud" class="d-flex flex-wrap justify-content-center gap-2">
            <!-- Wird dynamisch befüllt oder manuell ergänzt -->
            <p class="text-muted small">Wähle ein Thema, um die Suche zu verfeinern.</p>
        </div>
    </div>
</div>