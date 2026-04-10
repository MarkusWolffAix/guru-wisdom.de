<?php

declare(strict_types=1);

use Yiisoft\Html\Html;
use Yiisoft\View\WebView;

/**
 * @var WebView $this
 */

$title = 'Impressum';
$this->setTitle($title);

// In Yii3 werden Breadcrumbs oft über den Controller übergeben 
// oder als Parameter für das Layout gesetzt:
$this->setParameter('breadcrumbs', [$title]);

// Hinweis: "use yii\helpers\Url;" wurde entfernt, da es im originalen Code 
// nicht verwendet wurde. In Yii3 würde man dafür den UrlGeneratorInterface nutzen.
?>
<div class="site-contact">
    <h1><?= Html::encode($title) ?></h1>  
    
    <div class="w-50"> 
        <img src="/images/_MeColibriFestival.jpg" class="img-fluid" alt="Profilbild Markus Wolff">
    </div> 
    
    <address itemscope itemtype="https://schema.org/PostalAddress">
        <span itemprop="name">GURU Wisdom GmbH</span>
        <div class="w-100"></div> 
        
        <span itemprop="streetAddress">Sophienstrasse 26</span>
        <div class="w-100"></div> 
        
        <span itemprop="postalCode">52070</span>
        <span itemprop="addressLocality">Aachen</span>
        <div class="w-100"></div> 
        
        Geschäftsführer: Markus Wolff
        <div class="w-100"></div> 
        
        <span itemprop="telephone">Tel.: <a href="tel:+4924191992862" class="link-dark">+4924191992862</a></span>
        <div class="w-100"></div>
        
        <span itemprop="telephone">Mobil: <a href="tel:+4915560480346" class="link-dark">+4915560480346</a></span>
        <div class="w-100"></div> 
        
        Email: <span itemprop="email"><a href="mailto:info@guru-wisdom.com" class="link-dark">info@guru-wisdom.com</a></span>
    </address>

    <div class="w-100"></div> 
    Steuernummer 201/5963/5746
    
    <div class="w-100"></div>
    Registergericht: Amtsgericht Aachen
    
    <div class="w-100"></div>
    Registernummer: HRB 27892
    <div class="w-100"></div> 

    <br>
    § 5 TMG (Telemediengesetz) / § 55 RStV <br>
    Die Internetseite guru-wisdom.de ist Eigentum und unterliegt der Verantwortung der GURU Wisdom GmbH <br>
        
</div>
