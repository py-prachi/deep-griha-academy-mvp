<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\FeePaymentInterface;
use App\Repositories\FeePaymentRepository;

class FeePaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FeePaymentInterface::class, FeePaymentRepository::class);
    }
    public function boot() {}
}
