<?php

namespace Tests\Unit\Models;

use App\Models\SshConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SshConnectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_ssh_connection(): void
    {
        $connection = SshConnection::create([
            'name' => 'Test Server',
            'host' => 'example.com',
            'username' => 'testuser',
            'port' => 22,
            'password' => 'secret',
            'locked' => false
        ]);

        $this->assertDatabaseHas('ssh_connections', [
            'name' => 'Test Server',
            'host' => 'example.com',
            'username' => 'testuser',
            'port' => 22
        ]);

        $this->assertIsInt($connection->port);
    }
} 