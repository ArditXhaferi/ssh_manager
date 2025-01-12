<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\MenuController;
use App\Models\SshConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Native\Laravel\Facades\MenuBar;
use Tests\Traits\MocksShell;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase, MocksShell;

    private MenuController $menuController;

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuController = new MenuController();
    }

    public function test_refresh_menu_creates_menu_bar(): void
    {
        // Mock the MenuBar facade
        MenuBar::shouldReceive('create')
            ->once()
            ->andReturnSelf();
        
        MenuBar::shouldReceive('width')
            ->with(300)
            ->once()
            ->andReturnSelf();
            
        MenuBar::shouldReceive('height')
            ->with(600)
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

        $this->menuController->refreshMenu();
        
        $this->assertTrue(true); // Assert that we got here without exceptions
    }
} 