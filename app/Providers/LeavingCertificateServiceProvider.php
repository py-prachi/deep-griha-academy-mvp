<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\LeavingCertificateInterface;
use App\Repositories\LeavingCertificateRepository;

class LeavingCertificateServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(LeavingCertificateInterface::class, LeavingCertificateRepository::class);
    }

    public function boot()
    {
        //
    }
}
