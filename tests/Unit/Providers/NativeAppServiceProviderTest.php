<?php

namespace Tests\Unit\Providers;

use App\Providers\NativeAppServiceProvider;
use Tests\TestCase;
use Native\Laravel\Facades\MenuBar;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NativeAppServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_boot_initializes_menu(): void
    {
        // Mock the database query
        DB::shouldReceive('table')
            ->with('ssh_connections')
            ->andReturnSelf()
            ->shouldReceive('orderBy')
            ->with('updated_at', 'desc')
            ->andReturnSelf()
            ->shouldReceive('limit')
            ->with(3)
            ->andReturnSelf()
            ->shouldReceive('select')
            ->with('name', 'id')
            ->andReturn(collect([]));
            
        // Mock the MenuBar facade
        MenuBar::shouldReceive('create')
            ->once()
            ->andReturnSelf();
            
        MenuBar::shouldReceive('width')
            ->once()
            ->andReturnSelf();
            
        MenuBar::shouldReceive('height')
            ->once()
            ->andReturnSelf();
            
        MenuBar::shouldReceive('icon')
            ->once()
            ->andReturnSelf();
            
        MenuBar::shouldReceive('blendBackgroundBehindWindow')
            ->once()
            ->andReturnSelf();
            
        MenuBar::shouldReceive('withContextMenu')
            ->once()
            ->andReturnSelf();

        $provider = new NativeAppServiceProvider();
        $provider->boot();
        
        $this->assertTrue(true); // Assert that we got here without exceptions
    }
} 