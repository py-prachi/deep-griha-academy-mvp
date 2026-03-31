<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\StudentExitInterface;
use App\Repositories\StudentExitRepository;

class StudentExitServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(StudentExitInterface::class, StudentExitRepository::class);
    }

    public function boot()
    {
        //
    }
}
