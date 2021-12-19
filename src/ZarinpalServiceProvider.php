<?php

namespace Armincms\Zarinpal;

use Illuminate\Contracts\Support\DeferrableProvider; 
use Illuminate\Support\ServiceProvider; 

class ZarinpalServiceProvider extends ServiceProvider implements DeferrableProvider
{   
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        app('arminpay')->extend('zarinpal', function($app, $config) {
            return new Zarinpal($config);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /**
     * Get the events that trigger this service provider to register.
     *
     * @return array
     */
    public function when()
    {
        return [
            \Armincms\Arminpay\Events\ResolvingArminpay::class
        ];
    }
}
