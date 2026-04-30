<?php

declare(strict_types=1);

/** @var \Yiisoft\View\WebView $this */

$this->setTitle('Impressum');
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


<!-- Die Schriftrolle: Wisdom Liste -->
<section class="container pb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <!-- Scroll-Container -->
            <div class="wisdom-scroll-area pe-2" id="wisdomContainer">
                
                <!-- Karte 1 -->
                <div class="card mb-4 shadow-sm border-0 wisdom-card" data-category="spiritualitaet">
                    <div class="card-body p-4">
                        <h3 class="card-title h5">Das Geheimnis der Stille</h3>
                        <p class="card-text text-muted">In der absoluten Leere offenbart sich die Fülle des Seins...</p>
                        <div class="tags mt-3">
                            <span class="badge bg-light text-dark border">Meditation</span>
                            <span class="badge bg-light text-dark border">Achtsamkeit</span>
                        </div>
                    </div>
                </div>

                <!-- Karte 2 -->
                <div class="card mb-4 shadow-sm border-0 wisdom-card" data-category="wissenschaft">
                    <div class="card-body p-4">
                        <h3 class="card-title h5">Fraktale Geometrie</h3>
                        <p class="card-text text-muted">Die Muster der Natur wiederholen sich im Kleinen wie im Großen...</p>
                        <div class="tags mt-3">
                            <span class="badge bg-light text-dark border">Mathematik</span>
                            <span class="badge bg-light text-dark border">Natur</span>
                        </div>
                    </div>
                </div>
                
                <!-- Weitere Karten folgen hier... -->

            </div>
        </div>
    </div>
</section>


    