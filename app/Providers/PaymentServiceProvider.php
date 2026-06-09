<?php

namespace App\Providers;

use App\Payments\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        foreach (config('payment.gateways', []) as $method => $class) {
            $this->app->singleton($class);
        }

        $this->app->singleton(PaymentManager::class);
    }
}
