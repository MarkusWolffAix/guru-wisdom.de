<?php

declare(strict_types=1);

/** @var \Yiisoft\View\WebView $this */
/** @var array $wisdoms */

$this->setTitle('Übersicht der Weisheiten');
?>


<!-- flex-column zwingt die Elemente IMMER untereinander. flex-md-row wurde entfernt. -->
    <div class="container d-flex flex-column align-items-center justify-content-center">
        
        <!-- mb-3 gibt dem Bild einen schönen Abstand nach unten zum Text. Alle me-md-... Klassen sind weg. -->
        <div class="eye-container mb-3" style="max-width: 250px;">
            <img src="/images/icons/gurueye.webp" alt="Das wachende Auge der Erkenntnis" class="img-fluid">
        </div>
        
        <!-- text-center gilt jetzt für alle Geräte. text-md-start wurde entfernt. -->
        <div class="text-center">
            <p class="lead text-muted mb-0">Übersicht der Weisheiten</p>
        </div>

    </div>

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
                    <?php foreach ($wisdoms as $wisdom): ?>
                        <?php 
                            $id = $wisdom['id'] ?? $wisdom['slug'];
                            $title = $wisdom['title'] ?? 'Unbekannte Weisheit';
                            $subtitle = $wisdom['subtitle'] ?? '';
                            $tags = $wisdom['tags'] ?? [];
                            $tags = array_map(fn($item) => trim($item, '"'), $tags);
                            // SICHERHEIT: Kategorie sauber auslesen (egal ob Array oder String)
                            $categories = $wisdom['categories'] ?? ['Allgemein'];
                            $categories =array_map(fn($item) => trim($item, '"'), $categories);
                            $category = is_array($categories) ? ($categories[0] ?? 'Allgemein') : $categories;

                            $imageName = !empty($wisdom['image']) ? $wisdom['image'] : 'Urd';
                            $imagePathWebp = "https://media.guru-wisdom.de/images/thumb/{$id}.webp";
                            $imagePathJpg = "https://media.guru-wisdom.de/images/{$id}.jpg";
                        ?>

                        <div class="card wisdom-card p-3 shadow-sm border-0 mb-1" data-category="<?= htmlspecialchars((string)$category) ?>">
                            
                            <!-- Anklickbarer Bereich: Bild links, Text rechts -->
                            <a href="<?= htmlspecialchars((string)$id) ?>" class="wisdom-clickable-area text-decoration-none d-flex flex-row align-items-center">
                                
                                <!-- Vorschaubild (Links, Wächter-Container) -->
                                <div class="wisdom-thumb-container me-4">
                                    <picture class="wisdom-picture">
                                        <source srcset="<?= $imagePathWebp ?>" type="image/webp">
                                        <img src="<?= $imagePathJpg ?>" alt="<?= htmlspecialchars((string)$title) ?>" class="wisdom-thumb-img">
                                    </picture>
                                </div>
                                
                                <!-- Text-Inhalt (Rechts daneben) -->
                                <div class="wisdom-text-container flex-grow-1 text-start">
                                    <h3 class="card-title h5 fw-bold mb-2 text-dark">
                                        <?= htmlspecialchars((string)$title) ?>
                                    </h3>
                                    <p class="card-text text-muted mb-0 small">
                                        <?= htmlspecialchars((string)$subtitle) ?>
                                    </p>
                                </div>
                            </a>

                            <!-- Unterer Bereich: Kategorie & Tags getrennt -->
                            <div class="wisdom-tags-area mt-3 pt-2 mb-0 border-top">
                                
                                <!-- Zeile 1: Die Hauptkategorie -->
                                <div class="mb-2">
                                    <div class="d-flex flex-wrap justify-content-start gap-1">
                                    <?php foreach ($categories as $category): ?>
                                        <span class="badge bg-secondary text-light px-2 py-1" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars((string)$category) ?>
                                        </span>
                                    <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Zeile 2: Die Tags -->
                                <div class="d-flex flex-wrap justify-content-start gap-1">
                                    <?php foreach ($tags as $tag): ?>
                                        <span class="badge bg-light text-dark border-0 shadow-sm" style="font-size: 0.75rem;">
                                            <?= htmlspecialchars((string)$tag) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                
                            </div>
                        </div> <!-- WICHTIG: Das schließt die einzelne Karte -->
                    <?php endforeach; ?> <!-- WICHTIG: Das beendet die PHP-Schleife -->
                </div> <!-- WICHTIG: Das schließt den gesamten Scroll-Container -->

                <!-- Mystischer Runter-Button (Wieder an der richtigen Stelle!) -->
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