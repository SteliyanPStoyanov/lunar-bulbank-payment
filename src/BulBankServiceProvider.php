<?php

namespace Lunar\BulBank;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Lunar\BulBank\Http\Livewire\Payment;
use Lunar\Facades\Payments;

class BulBankServiceProvider extends ServiceProvider
{
    protected $root = __DIR__ . '/..';

    public function register()
    {

    }

    public function boot(): void
    {
        Payments::extend('bulbank', function ($app) {
            return $app->make(BulBankPaymentType::class);
        });

        $this->loadViewsFrom(__DIR__ . '/../resources', 'bulbank');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->mergeConfigFrom(__DIR__ . '/../config/bulbank.php', 'lunar.bulbank');

        $this->publishes([
            __DIR__ . '/../config/bulbank.php' => config_path('lunar/bulbank.php'),
        ], 'lunar.bulbank.config');

        $this->registerLivewireComponents();

    }

    public function registerLivewireComponents(): void
    {
        Livewire::component('bulbank.payment', Payment::class);


    }

}
