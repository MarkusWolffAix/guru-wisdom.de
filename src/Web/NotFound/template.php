<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

/** @var WebView $this */

$title = 'Pfad nicht gefunden – Guru Wisdom';
$this->setTitle($title);

// Meta-Tags für SEO: Verhindern, dass Google diese Fehlerseite fälschlicherweise indexiert
$this->registerMeta(['name' => 'robots', 'content' => 'noindex, nofollow'], 'robots');
?>

<div class="container d-flex flex-column align-items-center justify-content-center py-5" style="min-height: 70vh;">
    
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="mystic-scroll-bg p-4 p-md-5 shadow-lg text-center position-relative overflow-hidden" style="border-radius: 12px;">
                
                <div class="position-absolute top-50 start-50 translate-middle w-50" style="z-index: 0; opacity: 0.04; pointer-events: none;">
                    <picture>
                        <source srcset="/images/icons/gurueye.webp" type="image/webp">
                        <img src="/images/icons/gurueye.jpg" alt="" class="img-fluid">
                    </picture>
                </div>

                <div class="position-relative p-3" style="z-index: 1; background: radial-gradient(circle, rgba(255,255,255,0.85) 20%, rgba(255,255,255,0.3) 70%, rgba(255,255,255,0) 100%);">
                    
                    <div class="handwritten-text">
                        <h1 class="display-3 fw-bold text-dark mb-2">404</h1>
                        <h2 class="h3 mb-4">Dieser Pfad verliert sich im Nebel der Zeit.</h2>
                    </div>

                    <p class="text-dark fw-medium mb-4" style="font-size: 1.1rem; line-height: 1.6;">
                        Das Auge der Erkenntnis hat gesucht, doch die gewünschte Weisheit konnte an diesem Ort nicht gefunden werden. 
                        Vielleicht wurde die Schriftrolle verschoben, oder der Link war nur eine Illusion.
                    </p>
                    
                    <hr class="w-50 mx-auto mb-4 opacity-50">
                    
                    <p class="mb-4 font-italic text-dark">
                        „Manchmal ist das Verlaufen der erste Schritt, <br>um etwas völlig Neues zu entdecken.“
                    </p>

                    <a href="/" class="btn btn-outline-dark px-4 py-2 mt-2 rounded-pill shadow-sm" style="transition: all 0.3s ease;">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="me-2 mb-1">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        Zurück zum Archiv der Weisheiten
                    </a>
                </div> </div>
        </div>
    </div>
</div>

<style>
    /* Ein kleiner Hover-Effekt exklusiv für den Zurück-Button auf dieser Seite */
    .btn-outline-dark:hover {
        background-color: #2c3e50;
        color: #fff;
        transform: translateY(-2px);
    }
</style>