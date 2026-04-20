<?php

declare(strict_types=1);

// Import your new, independent Action classes here
use App\Web\Login\Action as LoginAction;
use App\Web\Contact\Action as ContactAction;
use App\Web\Impressum\Action as ImpressumAction;
use App\Web\PrivacyPolicy\Action as PrivacyPolicyAction;
use App\Web\WordsOfWisdom\Action as WordsOfWisdomAction;
use App\Web\Deployment\Action as DeploymentAction;

use Yiisoft\Router\Route;

return [
    // 🔐 Login
    Route::methods(['GET', 'POST'], '/login')
        ->action([LoginAction::class, 'handle'])
        ->name('login'),

    // ✉️ Contact
    Route::methods(['GET', 'POST'], '/kontakt')
        ->action([ContactAction::class, 'handle'])
        ->name('contact-de'),

    // ⚖️ Legal Notice (Impressum)
    Route::get('/impressum')
        ->action([ImpressumAction::class, 'handle'])
        ->name('impressum-de'),

    // 🛡️ Privacy Policy
    Route::get('/datenschutz') // German URL for privacy policy
        ->action([PrivacyPolicyAction::class, 'handle'])
        ->name('privacypolicy-de'),

    // 📜 Overview of all words of wisdom (Index)
    Route::get('/')
        ->action([WordsOfWisdomAction::class, 'handle'])
        ->name('wordsofwisdom.index-de'), // Unique name for the list

    // 🔍 Detailed view of ONE specific word of wisdom (View)
    Route::get('/{id:[\w-]+}')
        ->action([WordsOfWisdomAction::class, 'handle'])
        ->name('wordsofwisdom.view-de'), // Unique name for the detail view

    // 🚀 Deployment
    Route::get('/deploy')
        ->action([DeploymentAction::class, 'handle'])
        ->name('deploy'),
];
