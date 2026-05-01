<?php

declare(strict_types=1);

/** @var \Yiisoft\View\WebView $this */

$this->setTitle('Übersicht der Weisheiten');
?>
<<!-- Hero Sektion: Das Auge -->
<header class="py-5 text-center position-relative">
    <div class="container">
        <!-- Hier binden wir später das Bild oder die interaktive SVG des Auges ein -->
        <div class="eye-container mx-auto" style="max-width: 600px;">
            <img src="/images/icons/guru-eye.svg" alt="Das wachende Auge der Erkenntnis" class="img-fluid">
        </div>
        <h1 class="mt-4 fw-light">Guru Wisdom</h1>
        <p class="lead text-muted">Tritt ein und finde, was du suchst.</p>
    </div>
</header>


<!-- Navigations- und Filter-Bereich -->
<section class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <!-- Live-Suchleiste -->
            <div class="input-group input-group-lg mb-4 shadow-sm">
                <span class="input-group-text bg-dark text-light border-dark" id="search-icon">🔍</span>
                <input type="text" id="liveSearch" class="form-control bg-dark text-light border-dark" placeholder="Frage das Orakel..." aria-label="Suche">
            </div>

            <!-- Hauptkategorien als Filter-Pills -->
            <ul class="nav nav-pills justify-content-center mb-3" id="categoryFilters">
                <li class="nav-item">
                    <button class="nav-link active rounded-pill px-4 mx-1 bg-secondary text-white" data-filter="all">Alle</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 mx-1 text-dark" data-filter="spiritualitaet">Spiritualität</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link rounded-pill px-4 mx-1 text-dark" data-filter="wissenschaft">Wissenschaft</button>
                </li>
            </ul>

            <!-- Button für detaillierte Tags (öffnet Offcanvas) -->
            <div class="text-center">
                <button class="btn btn-outline-dark btn-sm rounded-pill" type="button" data-bs-toggle="offcanvas" data-bs-target="#tagOffcanvas">
                    Tiefer filtern (Tags)
                </button>
            </div>

        </div>
    </div>
</section>


<!-- Die Schriftrolle: Der magische Container -->
<section class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <!-- Der Schriftrollen-Hintergrund -->
            <div class="mystic-scroll-bg p-4 shadow-lg">
                
                <!-- Mystischer Hoch-Button -->
                <button id="scrollUpBtn" class="mystic-scroll-bar w-100 mb-4" aria-label="Zurück in die Vergangenheit">
                    <svg viewBox="0 0 24 24" width="40" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M 4 16 Q 12 4 20 16"></path>
                    </svg>
                </button>

                <!-- Scroll-Container -->
              <div class="wisdom-scroll-area" id="wisdomContainer">
    <?php foreach ($wisdoms as $wisdom): ?>
        <?php 
            // Sicherstellen, dass wir Fallback-Werte haben
            $id = $wisdom['id'] ?? $wisdom['slug']; // Je nachdem wie dein Array aufgebaut ist
            $title = $wisdom['title'] ?? 'Unbekannte Weisheit';
            $category = $wisdom['category'] ?? 'allgemein';
            $teaser = $wisdom['teaser'] ?? '';
            $tags = $wisdom['tags'] ?? [];
            
            // Bildpfad-Logik (Standardmäßig Urd, wenn nichts definiert ist)
            $imageName = !empty($wisdom['image']) ? $wisdom['image'] : 'Urd';
            $imagePathWebp = "https://media.guru-wisdom.de/images/thumb/{$imageName}.webp";
            $imagePathJpg = "https://media.guru-wisdom.de/images/thumb/{$imageName}.jpg";
        ?>

        <a href="https://guru-wisdom.de/<?= $id ?>" 
           class="text-decoration-none wisdom-link-wrapper mb-2">
            <div class="card wisdom-card flex-row align-items-center p-3 shadow-sm border-0" 
                 data-category="<?= htmlspecialchars($category) ?>">
                
                <!-- Vorschaubild (90px Wächter-Container) -->
                <div class="wisdom-thumb-container me-4">
                    <picture class="wisdom-picture">
                        <source srcset="<?= $imagePathWebp ?>" type="image/webp">
                        <img src="<?= $imagePathJpg ?>" 
                             alt="<?= htmlspecialchars($title) ?>" 
                             class="wisdom-thumb-img">
                    </picture>
                </div>
                
                <!-- Text-Inhalt -->
                <div class="card-body p-0">
                    <h3 class="card-title h5 fw-bold mb-2 text-dark">
                        <?= htmlspecialchars($title) ?>
                    </h3>
                    <p class="card-text text-muted mb-2">
                        <?= htmlspecialchars($teaser) ?>
                    </p>
                    <div class="tags">
                        <?php foreach ($tags as $tag): ?>
                            <span class="badge bg-light text-dark border">
                                <?= htmlspecialchars($tag) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </a>
    <?php endforeach; ?>
</div>





    