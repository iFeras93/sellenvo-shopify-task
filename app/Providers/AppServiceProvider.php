<?php

namespace App\Providers;

use App\Contracts\ProductApiInterface;
use App\Factories\ShopifyProductApi;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductApiInterface::class, ShopifyProductApi::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
