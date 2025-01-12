<?php

namespace Tests\Feature\Controllers;

use App\Models\SshConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SshConnectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_connections_in_correct_order(): void
    {
        // Create connections in random order
        SshConnection::create([
            'name' => 'Second',
            'host' => 'second.com',
            'username' => 'user2',
            'port' => 22,
            'created_at' => now()->subDay()
        ]);

        SshConnection::create([
            'name' => 'First',
            'host' => 'first.com',
            'username' => 'user1',
            'port' => 22,
            'created_at' => now()
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200)
            ->assertViewHas('connections')
            ->assertSeeInOrder(['First', 'Second']);
    }
} 