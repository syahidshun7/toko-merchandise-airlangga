<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('store.layout', function ($view) {
            if (! array_key_exists('cartCount', $view->getData())) {
                $view->with('cartCount', app(CartService::class)->count(request()->session()));
            }
        });
    }
}
