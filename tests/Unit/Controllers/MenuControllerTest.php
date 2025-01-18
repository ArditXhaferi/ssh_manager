<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\MenuController;
use App\Models\SshConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Native\Laravel\Facades\MenuBar;
use Native\Laravel\Facades\Menu;
use Native\Laravel\Facades\Window;
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

    public function test_build_menu_with_no_connections(): void
    {
        $menu = $this->menuController->buildMenu();
        
        $this->assertNotNull($menu);
        
        // Get the menu items array through reflection
        $reflection = new \ReflectionClass($menu);
        $property = $reflection->getProperty('items');
        $property->setAccessible(true);
        $items = $property->getValue($menu);
        
        // Verify the menu structure
        $this->assertCount(5, $items); // Header, separator, "No recent connections", separator, quit
        
        // Get label through reflection for each menu item
        $labelReflection = new \ReflectionClass($items[0]);
        $labelProperty = $labelReflection->getProperty('label');
        $labelProperty->setAccessible(true);
        
        $this->assertEquals('Recent Connections', $labelProperty->getValue($items[0]));
        $this->assertEquals('No recent connections', $labelProperty->getValue($items[2]));
    }

    public function test_build_menu_with_connections(): void
    {
        // Create test connections
        SshConnection::create([
            'name' => 'Test Server 1',
            'host' => 'test1.com',
            'username' => 'user1',
            'port' => 22
        ]);

        SshConnection::create([
            'name' => 'Test Server 2',
            'host' => 'test2.com',
            'username' => 'user2',
            'port' => 22
        ]);

        $menu = $this->menuController->buildMenu();
        
        // Get menu items through reflection
        $reflection = new \ReflectionClass($menu);
        $property = $reflection->getProperty('items');
        $property->setAccessible(true);
        $items = $property->getValue($menu);
        
        // Verify menu contains connections
        $this->assertGreaterThan(4, count($items)); // Should have more items than the empty menu
        
        // Get label through reflection for the first item
        $labelReflection = new \ReflectionClass($items[0]);
        $labelProperty = $labelReflection->getProperty('label');
        $labelProperty->setAccessible(true);
        
        $this->assertEquals('Recent Connections', $labelProperty->getValue($items[0]));
        
        // Check if connection names are in the menu using reflection
        $menuLabels = array_map(function($item) {
            $labelReflection = new \ReflectionClass($item);
            $labelProperty = $labelReflection->getProperty('label');
            $labelProperty->setAccessible(true);
            return $labelProperty->getValue($item);
        }, $items);
        
        $this->assertTrue(in_array('1 | Test Server 1', $menuLabels));
        $this->assertTrue(in_array('2 | Test Server 2', $menuLabels));
    }

    public function test_refresh_menu_creates_menu_bar(): void
    {
        // Mock Window facade
        Window::shouldReceive('open')
            ->once()
            ->andReturnSelf();
            
        Window::shouldReceive('width')
            ->with(800)
            ->once()
            ->andReturnSelf();
            
        Window::shouldReceive('height')
            ->with(800)
            ->once()
            ->andReturnSelf();

        // Mock MenuBar facade
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
            ->with(public_path('menuBarIcon.png'))
            ->once()
            ->andReturnSelf();
            
        MenuBar::shouldReceive('blendBackgroundBehindWindow')
            ->once()
            ->andReturnSelf();
            
        MenuBar::shouldReceive('withContextMenu')
            ->once()
            ->andReturnSelf();

        $this->menuController->refreshMenu();
        
        // Verify all expected method calls occurred
        $this->addToAssertionCount(1);
    }
} 