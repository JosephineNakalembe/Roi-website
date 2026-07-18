<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadHelpers();
    }

    protected function loadHelpers(): void
    {
        require_once app_path('Helpers/helpers.php');
    }
}
