<?php

namespace NinePay;

use Illuminate\Support\ServiceProvider;
use NinePay\Config\NinePayConfig;
use NinePay\Contracts\PaymentGatewayInterface;
use NinePay\Gateways\NinePayGateway;
use NinePay\Utils\HttpClient;

class NinePayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ninepay.php', 'ninepay');

        $this->app->singleton('ninepay', function ($app) {
            $config = $app['config']->get('ninepay');
            return new NinePayGateway(NinePayConfig::fromArray($config), new HttpClient());
        });

        $this->app->bind(PaymentGatewayInterface::class, NinePayGateway::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ninepay.php' => base_path('config/ninepay.php'),
            ], 'ninepay-config');
        }
    }
}
