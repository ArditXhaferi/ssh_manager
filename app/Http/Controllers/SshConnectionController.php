<?php

namespace App\Http\Controllers;

use App\Models\SshConnection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Log;
use App\Helpers\SSHHelper;

class SshConnectionController extends Controller
{
    public function index()
    {
        $connections = SshConnection::orderBy('created_at', 'desc')->get();

        // Test each connection
        $connections->transform(function ($connection) {
            $connection->is_healthy = $this->testConnection($connection);
            return $connection;
        });

        return view('home', compact('connections'));
    }
    private function testConnection($connection)
    {
        Log::info('Testing SSH connection', [
            'connection_id' => $connection->id,
            'name' => $connection->username,
            'host' => $connection->host,
            'port' => $connection->port
        ]);

        $cacheKey = "ssh_health_{$connection->id}";

        return Cache::remember($cacheKey, 30, function () use ($connection) {
            $result = SSHHelper::testSSHConnection(
                $connection->host,
                $connection->username,
                $connection->password,
                $connection->port
            );

            // Log the result
            if ($result === true) {
                Log::info('SSH connection test successful', [
                    'connection_id' => $connection->id,
                    'name' => $connection->username
                ]);
            } else {
                Log::warning('SSH connection test failed', [
                    'connection_id' => $connection->id,
                    'name' => $connection->username,
                    'error' => $result
                ]);
            }

            return $result === true;
        });
    }

}