<?php

declare(strict_types=1);

/**
 * @var \Yiisoft\View\WebView $this
 * @var string $content
 * @var \Yiisoft\Assets\AssetManager $assetManager
 * @var \Yiisoft\Router\UrlGeneratorInterface $urlGenerator
 * @var \Yiisoft\User\CurrentUser $currentUser
 * @var \Psr\Http\Message\ServerRequestInterface $request
 * @var string $environment
 * @var string $language
 * @var \Yiisoft\Translator\TranslatorInterface $translator
 */

use App\Web\Shared\Layout\Main\MainAsset;
use Yiisoft\Html\Html;
use Yiisoft\Bootstrap5\Nav;
use Yiisoft\Bootstrap5\NavLink;
use Yiisoft\Bootstrap5\NavBar;

$assetManager->register(MainAsset::class);
$lang = $translator->getLocale(); 

$this->addCssFiles($assetManager->getCssFiles());
$this->addJsFiles($assetManager->getJsFiles());


$this->registerMeta(['charset' => 'UTF-8'], 'charset');
$this->registerMeta(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no'], 'viewport');
$this->registerMeta(['name' => 'description', 'content' => $this->getParameter('meta_description', '')], 'description');
$this->registerMeta(['name' => 'keywords', 'content' => $this->getParameter('meta_keywords', '')], 'keywords');

$this->registerLink(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => '/favicon.ico']);

 

$this->beginPage();
?>
<!DOCTYPE html>
<html lang="<?= Html::encode($lang ?? 'de') ?>" class="h-100">
<head>
    <title><?= Html::encode($this->getTitle()) ?></title>    
    <?php $this->head() ?>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    $brandLabel = Html::img('/images/logo/GuruWisdom.png', 'Logo Guru Wisdom')
        ->id('brand-logo')
        ->addStyle('width:32px;')
        ->class('img-fluid');

    echo NavBar::widget()
        ->brandText((string) $brandLabel)
        ->brandUrl($urlGenerator->generate('wordsofwisdom.index-'.$lang))
        ->attributes(['class' => 'navbar-expand-md navbar-light bg-white fixed-top'])
        ->begin();


        
/*
  $menuItems = [
        NavLink::to('Weisheiten')->url($urlGenerator->generate('wordsofwisdom.index')),
        NavLink::to('Impressum')->url($urlGenerator->generate('impressum')),
        NavLink::to('Datenschutz')->url($urlGenerator->generate('privacypolicy')),
        NavLink::to('Kontakt')->url($urlGenerator->generate('contact')),
    ];

*/


   /* if ($currentUser->isGuest()) {
        if (isset($environment) && $environment !== 'prod') {
            $menuItems[] = ['label' => 'Login', 'url' => $urlGenerator->generate('login')];
        }
    } else {
        $username = $currentUser->getIdentity()->get('username') ?? 'User';
        $menuItems[] = '<li class="nav-item">'
            . Html::form()->action($urlGenerator->generate('logout'))->open()
            . Html::submitButton(
                'Logout (' . Html::encode($username) . ')',
                ['class' => 'nav-link btn btn-link logout']
            )
            . Html::form()->close()
            . '</li>';
    }*/

   /* echo Nav::widget()
    ->attributes(['class' => 'navbar-nav mx-auto mb-2 mb-md-0'])
    ->items(...$menuItems);
*/
    echo NavBar::end();

?>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php /* Falls Breadcrumbs benötigt werden: echo Breadcrumbs::widget()->links($this->getParameter('breadcrumbs', [])); */ ?>
        <?= '' /* Alert::widget() - Bitte prüfen, ob das Alert-Widget für Yii3 portiert wurde */ ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto py-3 bg-white text-white">
    <div class="container">
        <div class="row text-muted">
    
        <div class="container text-center">
        <div class="mb-3">
            <a href="https://www.youtube.com/@guru2wisdom" class="me-3"><img src="/images/icons/Youtube.png" alt="Youtube" width="24"></a>
            <a href="https://www.instagram.com/guru2wisdom" clsse="me-3" ><img src="/images/icons/Instagram.png" alt="Instagram" width="16"></a>
        </div>
        


        <div class="small text-muted mb-2">
            <a href="<?= $urlGenerator->generate('contact-' . $lang) ?>" class="text-decoration-none text-muted mx-2">
                <?= $translator->translate('menu.contact', [], 'app') ?>
            </a> |
    
            <a href="<?= $urlGenerator->generate('impressum-' . $lang) ?>" class="text-decoration-none text-muted mx-2">
              <?= $translator->translate('menu.imprint', [], 'app') ?>
            </a> |
    
            <a href="<?= $urlGenerator->generate('privacypolicy-' . $lang) ?>" class="text-decoration-none text-muted mx-2">
                 <?= $translator->translate('menu.privacy', [], 'app') ?>
            </a>
            </div>

        <div class="text-muted">
            <small>&copy; GURU Wisdom <?= date('Y') ?></small>
        </div>
        </div>
            <?php
            if (isset($environment) && $environment === 'test') {
                $deployUrl = $urlGenerator->generate('deploy', ['token' => 'DEIN_GEHEIMER_TOKEN_123']);
        
                echo Html::a('Deploy Test to Prod', $deployUrl, [
                    'class' => 'btn btn-outline-danger',
                    'onclick' => "return confirm('Wirklich die Test-Version in die Produktion überführen?');"
                ]);
            }
            ?>
            
            <?php
            if (isset($environment) && $environment === 'test') {
                exec("git log -1 --format=%at | xargs -I{} date -d @{} +%d.%m.%Y_%H:%M:%S", $outstage, $resstage);
                $outstageStr = implode('', $outstage);
                echo "<h5 style='color:green'>Test Version: " . Html::encode($outstageStr) . "</h5>";
                
                chdir("/var/www/prod.guru-wisdom.de");
                exec("git log -1 --format=%at | xargs -I{} date -d @{} +%d.%m.%Y_%H:%M:%S", $outprod, $resprod);
                $outprodStr = implode('', $outprod);
                echo "<h5 style='color:red'>Prod Version: " . Html::encode($outprodStr) . "</h5>";
                
                if ($outstageStr !== $outprodStr) {
                    $deploySelfUrl = $urlGenerator->generate('index', ['DoDeployTest2prod' => 'yes']); // Passe den Route-Namen hier an
                    echo Html::button('Deploy Test to Prod', [
                        'class' => 'btn btn-light btn-lg btn-block btn-outline-danger',
                        'onclick' => 'document.location.href="' . $deploySelfUrl . '"'
                    ]);
                    
                    $queryParams = $request->getQueryParams();
                    if (isset($queryParams['DoDeployTest2prod']) && $queryParams['DoDeployTest2prod'] === 'yes') {
                        exec("git pull", $outprodPull, $resprodPull);
                        echo "<h5 style='color:red'>" . Html::encode(implode("\n", $outprodPull)) . "</h5>";
                    }
                }
            }
            ?>
        </div>
    </div>
</footer>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
