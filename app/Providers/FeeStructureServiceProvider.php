<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\FeeStructureInterface;
use App\Repositories\FeeStructureRepository;

class FeeStructureServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FeeStructureInterface::class, FeeStructureRepository::class);
    }
    public function boot() {}
}
