<?php

namespace Tests\Unit\Providers;

use App\Providers\AppServiceProvider;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_provider_boots_successfully(): void
    {
        $provider = new AppServiceProvider($this->app);
        
        $provider->register();
        $provider->boot();
        
        $this->assertTrue(true); // Assert that we got here without exceptions
    }
} 