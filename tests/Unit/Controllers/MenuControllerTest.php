<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\MenuController;
use App\Models\SshConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_menu_with_recent_connections(): void
    {
        SshConnection::create([
            'name' => 'Test Server 1',
            'host' => 'example1.com',
            'username' => 'user1',
            'port' => 22,
            'updated_at' => now()
        ]);

        SshConnection::create([
            'name' => 'Test Server 2',
            'host' => 'example2.com',
            'username' => 'user2',
            'port' => 22,
            'updated_at' => now()->subMinute()
        ]);

        $menuController = new MenuController();
        $menu = $menuController->buildMenu();

        $this->assertNotNull($menu);
        $this->assertIsArray($menu->toArray());
        $this->assertStringContainsString('Test Server 1', $menu->toArray()["submenu"][2]['label']);
        $this->assertStringContainsString('Test Server 2', $menu->toArray()["submenu"][3]['label']);
    }

    public function test_build_menu_with_no_connections(): void
    {
        $menuController = new MenuController();
        $menu = $menuController->buildMenu();

        $this->assertNotNull($menu);
        $this->assertEquals('No recent connections', $menu->toArray()["submenu"][2]['label']);
    }
} 