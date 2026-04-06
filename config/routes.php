use Yiisoft\Router\Route;
use App\Controller\SiteController;

return [
    // Eine einfache Route für die Startseite
    Route::get('/')
        ->action([SiteController::class, 'index'])
        ->name('home'),
];
