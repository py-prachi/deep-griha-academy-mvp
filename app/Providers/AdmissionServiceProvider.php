<?php

namespace App\Providers;

use App\Interfaces\AdmissionInterface;
use App\Repositories\AdmissionRepository;
use Illuminate\Support\ServiceProvider;

class AdmissionServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(AdmissionInterface::class, AdmissionRepository::class);
    }

    public function boot()
    {
        //
    }
}
