<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\SshConnectionController;
use App\Models\SshConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SshConnectionControllerTest extends TestCase
{
    use RefreshDatabase;

    private SshConnectionController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new SshConnectionController();
    }

    public function test_index_returns_connections_view(): void
    {
        // Create some test connections
        SshConnection::create([
            'name' => 'Test Server',
            'host' => 'test.com',
            'username' => 'testuser',
            'port' => 22
        ]);

        $response = $this->controller->index();
        
        $this->assertEquals('home', $response->name());
        $this->assertNotEmpty($response->getData()['connections']);
    }
} 