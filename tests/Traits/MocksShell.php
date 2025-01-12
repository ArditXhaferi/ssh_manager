<?php

namespace Tests\Traits;

use Native\Laravel\Facades\Shell;
use Mockery;

trait MocksShell
{
    protected function mockShell()
    {
        if (!$this->app) {
            $this->refreshApplication();
        }
        
        $mock = Mockery::mock(Shell::class);
        $mock->shouldReceive('openFile')->andReturn(null);
        $this->app->instance(Shell::class, $mock);
        
        return $mock;
    }
} 