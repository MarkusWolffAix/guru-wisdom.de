<?php

declare(strict_types=1);

use App\Web\Contact\Action as ContactAction;
use App\Web\Deployment\Action as DeploymentAction;
use App\Web\Imprint\Action as ImprintAction;
use App\Web\Login\Action as LoginAction;
use App\Web\Privacy\Action as PrivacyAction;
use App\Web\WordsOfWisdom\Action as WordsOfWisdomAction;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\Router\Route;

return [
    // --- Standard-Routen (Englische Pfade & neutrale Namen) ---
    // 🔐 Login
    Route::methods(['GET', 'POST'], '/login')
        ->name('login')
        ->action([LoginAction::class, 'handle']),

    // ✉️ Contact
    Route::methods(['GET', 'POST'], '/contact')
        ->name('contact')
        ->action([ContactAction::class, 'handle']),

    // ⚖️ Legal Notice (Impressum)
    Route::get('/imprint')
        ->name('imprint')
        ->action([ImprintAction::class, 'handle']),

    // 🛡️ Privacy 
    Route::get('/privacy')
        ->name('privacy')
        ->action([PrivacyAction::class, 'handle']),

    // 🚀 Deployment
    Route::get('/deploy')
        ->name('deploy')
        ->action([DeploymentAction::class, 'handle']),

    // 📜 Overview of all words of wisdom (Index)
    Route::get('/')
        ->name('wordsofwisdom.index')
        ->action([WordsOfWisdomAction::class, 'handle']),

    // --- 301 SEO-Redirects old Path ---
    // --- 301 Permanent SEO Redirects (Legacy German URLs -> New Canonical English URLs) ---
    // Added: 2026-11-02
    // SEO Best Practice: Retain for at least 12 months (until November 2027 minimum)
    // to allow Googlebot to consolidate index signals and transfer full link equity.
    // Recommended: Keep indefinitely to preserve external backlinks and user bookmarks.

    // ✉️ Contact (alt -> neu)
    Route::get('/kontakt')
        ->action(static fn (ResponseFactoryInterface $rf): ResponseInterface => $rf->createResponse(301)->withHeader('Location', '/contact')),

    // ⚖️ Legal Notice (alt -> neu)
    Route::get('/impressum')
        ->action(static fn (ResponseFactoryInterface $rf): ResponseInterface => $rf->createResponse(301)->withHeader('Location', '/imprint')),

    // 🛡️ Privacy Policy (alt -> neu)
    Route::get('/datenschutz')
        ->action(static fn (ResponseFactoryInterface $rf): ResponseInterface => $rf->createResponse(301)->withHeader('Location', '/privacy')),

    // 🔍 Detailed view of ONE specific word of wisdom (Muss ganz unten bleiben!)
    Route::get('/{id:[\w-]+}')
        ->name('wordsofwisdom.view')
        ->action([WordsOfWisdomAction::class, 'handle']),
];

