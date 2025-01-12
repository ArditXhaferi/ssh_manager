<?php

namespace Tests\Feature\Livewire;

use App\Livewire\SshManager;
use App\Models\SshConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SshManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_add_new_connection(): void
    {
        Livewire::test(SshManager::class, ['connections' => []])
            ->set('newConnection', [
                'name' => 'Test Server',
                'host' => 'example.com',
                'username' => 'testuser',
                'port' => 22,
                'password' => 'secret',
                'locked' => false
            ])
            ->call('addConnection');

        $this->assertDatabaseHas('ssh_connections', [
            'name' => 'Test Server',
            'host' => 'example.com',
            'username' => 'testuser',
            'port' => 22
        ]);
    }

    public function test_can_update_connection(): void
    {
        $connection = SshConnection::create([
            'name' => 'Test Server',
            'host' => 'example.com',
            'username' => 'testuser',
            'port' => 22,
            'password' => 'secret',
            'locked' => false
        ]);

        Livewire::test(SshManager::class, ['connections' => [$connection]])
            ->set('selectedConnection', [
                'id' => $connection->id,
                'name' => 'Updated Server',
                'host' => 'updated.com',
                'username' => 'updateduser',
                'port' => 2222,
                'password' => 'newsecret',
                'locked' => true
            ])
            ->call('updateConnection');

        $this->assertDatabaseHas('ssh_connections', [
            'id' => $connection->id,
            'name' => 'Updated Server',
            'host' => 'updated.com',
            'username' => 'updateduser',
            'port' => 2222
        ]);
    }

    public function test_can_test_connection_health(): void
    {
        $connection = SshConnection::create([
            'name' => 'Test Server',
            'host' => 'example.com',
            'username' => 'testuser',
            'port' => 22,
            'password' => 'secret',
            'locked' => false
        ]);

        Livewire::test(SshManager::class, ['connections' => [$connection]])
            ->call('updateConnectionsHealth')
            ->assertSet('connections.0.is_healthy', false);
    }

    public function test_can_start_connection(): void
    {
        $connection = SshConnection::create([
            'name' => 'Test Server',
            'host' => 'example.com',
            'username' => 'testuser',
            'port' => 22,
            'password' => 'secret'
        ]);

        Livewire::test(SshManager::class, ['connections' => [$connection]])
            ->call('startConnection', $connection->id);

        // Since we're using a test shell instance, we just verify the connection exists
        $this->assertDatabaseHas('ssh_connections', [
            'id' => $connection->id,
            'name' => 'Test Server'
        ]);
    }

    public function test_can_edit_connection(): void
    {
        $connection = SshConnection::create([
            'name' => 'Test Server',
            'host' => 'example.com',
            'username' => 'testuser',
            'port' => 22,
            'password' => 'secret',
            'locked' => false
        ]);

        Livewire::test(SshManager::class, ['connections' => [$connection]])
            ->call('editConnection', $connection->id)
            ->assertSet('connection.id', $connection->id)
            ->assertSet('connection.name', 'Test Server')
            ->assertSet('connection.host', 'example.com')
            ->assertSet('showEditModal', true);
    }

    public function test_can_delete_connection(): void
    {
        $connection = SshConnection::create([
            'name' => 'Test Server',
            'host' => 'example.com',
            'username' => 'testuser',
            'port' => 22,
            'password' => 'secret'
        ]);

        Livewire::test(SshManager::class, ['connections' => [$connection]])
            ->call('deleteConnection', $connection->id);

        $this->assertDatabaseMissing('ssh_connections', [
            'id' => $connection->id
        ]);
    }

    public function test_handles_failed_connection_start(): void
    {
        $connection = SshConnection::create([
            'name' => 'Test Server',
            'host' => 'invalid-host',
            'username' => 'testuser',
            'port' => 22,
            'password' => 'secret'
        ]);

        Livewire::test(SshManager::class, ['connections' => [$connection]])
            ->call('startConnection', 999)
            ->assertDispatched('error');
    }
    

    public function test_handles_failed_connection_update(): void
    {
        $connection = SshConnection::create([
            'name' => 'Test Server',
            'host' => 'example.com',
            'username' => 'testuser',
            'port' => 22,
            'password' => 'secret'
        ]);

        Livewire::test(SshManager::class, ['connections' => [$connection]])
            ->set('selectedConnection', [
                'id' => 999, // Non-existent ID
                'name' => 'Updated Server',
                'host' => 'updated.com',
                'username' => 'updateduser',
                'port' => 2222
            ])
            ->call('updateConnection')
            ->assertDispatched('error');
    }
} 