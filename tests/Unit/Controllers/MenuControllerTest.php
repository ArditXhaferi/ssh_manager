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
        // Create some test connections
        SshConnection::create([
            'name' => 'Test Server 1',
            'host' => 'example1.com',
            'username' => 'user1',
            'port' => 22
        ]);

        SshConnection::create([
            'name' => 'Test Server 2',
            'host' => 'example2.com',
            'username' => 'user2',
            'port' => 22
        ]);

        $menuController = new MenuController();
        $menu = $menuController->buildMenu();

        $this->assertNotNull($menu);
        // Add more specific assertions based on your menu structure
    }
} 