<?php

namespace App\Providers;

use App\Http\Controllers\MenuController;
use Native\Laravel\Facades\MenuBar;

class NativeAppServiceProvider
{
    public function boot(): void
    {
        $menuController = new MenuController();
        $menuController->refreshMenu();
    }
}