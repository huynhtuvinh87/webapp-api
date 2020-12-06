<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapAuthRoutes();

        $this->mapNotificationRoutes();

        $this->mapTaskRoutes();

        $this->mapEmployeeRoutes();

        $this->mapContractorRoutes();

        $this->mapHiringOrganizationRoutes();

        $this->mapReportRoutes();

        $this->mapWorkTypeRoutes();

        $this->mapLocationRoutes();

        $this->mapWebRoutes();

        $this->mapDynamicFormRoutes();

        $this->mapModuleRoutes();

        $this->mapRatingRoutes();

        $this->mapFileRoutes();

        $this->mapTranslationRoutes();

        $this->mapFolderRoutes();

        $this->mapLockRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    protected function mapAuthRoutes()
    {
        Route::prefix('api/auth')
            ->middleware('api')
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/auth.php'));
    }

    protected function mapTaskRoutes()
    {
        Route::prefix('api/task')
            ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/task.php'));
    }

    protected function mapEmployeeRoutes()
    {
        Route::prefix('api/employee')
            ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/employee.php'));
    }

    protected function mapContractorRoutes()
    {
        Route::prefix('api/contractor')
            ->middleware(['api', 'auth:api', 'isContractorAdmin'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/contractor.php'));
    }

    protected function mapHiringOrganizationRoutes(){
        Route::prefix('api/organization')
            ->middleware(['api',  'auth:api', 'isOrganizationAdmin'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/hiring-organization.php'));
    }

    protected function mapNotificationRoutes()
    {
        Route::prefix('api/notifications')
            ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/notifications.php'));
    }

    protected function mapReportRoutes()
    {
        Route::prefix('api/reports')
            ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/reports.php'));
    }

    protected function mapWorkTypeRoutes(){
        Route::prefix('api/work-types')
            ->middleware(['api'])
            ->namespace('App\Http\Controllers\Api')
            ->group(base_path('routes/worktypes.php'));
    }

    protected function mapLocationRoutes(){
        Route::prefix('api/location')
            ->middleware(['api'])
            ->namespace('App\Http\Controllers\Api')
            ->group(base_path('routes/location.php'));
    }

    protected function mapDynamicFormRoutes(){
        Route::prefix('api/forms')
            ->middleware(['api'])
            ->namespace('App\Http\Controllers\Api')
            ->group(base_path('routes/dynamic-forms.php'));
    }

    protected function mapModuleRoutes()
    {
        Route::prefix('api/module')
            ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/module.php'));
    }

    protected function mapRatingRoutes()
    {
        Route::prefix('api/ratings')
            ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/rating.php'));
    }

    protected function mapFileRoutes()
    {
        Route::prefix('api/files')
            ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/files.php'));
    }

    protected function mapTranslationRoutes()
    {
        Route::prefix('api/translations')
            // ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/translations.php'));
    }

    protected function mapFolderRoutes()
    {
        Route::prefix('api/folders')
            ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/folder.php'));
    }

    protected function mapLockRoutes()
    {
        Route::prefix('api/lock')
            ->middleware(['api', 'auth:api'])
            ->namespace('\App\Http\Controllers\Api')
            ->group(base_path('routes/lock.php'));
    }
}
