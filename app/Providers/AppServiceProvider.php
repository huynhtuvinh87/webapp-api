<?php

namespace App\Providers;

use App\Models\Contractor;
use App\Models\DynamicForm;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Models\Test;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            "contractor" => Contractor::class,
            "hiring_organization" => HiringOrganization::class,
            "role" => Role::class,
            "test" => Test::class,
            "form" => DynamicForm::class,
            "internal_document" => DynamicForm::class,
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Cashier::ignoreMigrations();
    }
}
