<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SshConnection;
use Native\Laravel\Facades\Shell;
use Illuminate\Support\Facades\Log;
use Native\Laravel\Facades\MenuBar;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Cache;
use App\Actions\ReturnSignature;
use App\Helpers\SSHHelper;

class SshManager extends Component
{
    public $connections = [];
    public $showNewModal = false;
    public $showEditModal = false;
    public $selectedConnection = null;
    public $newConnection = [
        'name' => '',
        'host' => '',
        'username' => '',
        'port' => 22,
        'password' => '',
        'locked' => false
    ];
    public $connection = [
        'name' => '',
        'host' => '',
        'username' => '',
        'port' => 22,
        'password' => '',
        'locked' => false
    ];

    protected $listeners = [
        'MenuBarShown' => 'updateConnectionsHealth'
    ];

    public function mount($connections)
    {
        $this->connections = $connections;
        $this->updateConnectionsHealth();
    }

    public function addConnection()
    {
        try {
            Log::info('Creating new SSH connection', [
                'name' => $this->newConnection['name'],
                'host' => $this->newConnection['host'],
                'username' => $this->newConnection['username'],
                'port' => $this->newConnection['port']
            ]);

            // Create new SshConnection record in database
            $connection = SshConnection::create([
                'name' => $this->newConnection['name'],
                'host' => $this->newConnection['host'],
                'username' => $this->newConnection['username'],
                'port' => $this->newConnection['port'],
                'password' => $this->newConnection['password'],
                'locked' => $this->newConnection['locked']
            ]);

            // Add the new connection to the local array
            $this->connections[] = $connection->toArray();

            $this->showNewModal = false;
            $this->newConnection = [
                'name' => '',
                'host' => '',
                'username' => '',
                'port' => 22,
                'password' => '',
                'locked' => false
            ];

            session()->flash('message', 'Connection added successfully.');

            Log::info('Successfully added new SSH connection', ['name' => $this->newConnection['name']]);
        } catch (\Exception $e) {
            Log::error('Failed to add SSH connection', [
                'error' => $e->getMessage(),
                'connection' => $this->newConnection['name']
            ]);
            session()->flash('error', 'Error adding connection.');
        }
    }

    public function editConnection($id)
    {
        $connection = collect($this->connections)->firstWhere('id', $id);
        $this->connection = [
            'id' => $connection['id'],
            'name' => $connection['name'],
            'host' => $connection['host'],
            'username' => $connection['username'],
            'port' => $connection['port'],
            'password' => $connection['password'] ?? '',
            'locked' => $connection['locked'] ?? false
        ];
        $this->showEditModal = true;
    }

    public function updateConnection()
    {
        try {
            Log::info('Updating SSH connection', [
                'connection_id' => $this->selectedConnection['id'],
                'name' => $this->selectedConnection['name'],
                'host' => $this->selectedConnection['host'],
                'username' => $this->selectedConnection['username'],
                'port' => $this->selectedConnection['port']
            ]);

            // Update the database record
            $connection = SshConnection::findOrFail($this->selectedConnection['id']);
            $connection->update([
                'name' => $this->selectedConnection['name'],
                'host' => $this->selectedConnection['host'],
                'username' => $this->selectedConnection['username'],
                'port' => $this->selectedConnection['port'],
                'password' => $this->selectedConnection['password'],
                'locked' => $this->selectedConnection['locked']
            ]);

            // Update the local array
            Log::info('Updating local connections array', [
                'connection_id' => $this->selectedConnection['id'],
                'old_connection' => collect($this->connections)->firstWhere('id', $this->selectedConnection['id']),
                'new_connection' => $this->selectedConnection
            ]);

            $this->connections = collect($this->connections)->map(function ($item) {
                if ($item['id'] === $this->selectedConnection['id']) {
                    return $this->selectedConnection;
                }
                return $item;
            })->toArray();

            Log::info('Local connections array updated successfully');

            $this->showEditModal = false;
            session()->flash('message', 'Connection updated successfully.');

            Log::info('Successfully updated SSH connection', [
                'connection_id' => $this->selectedConnection['id'],
                'name' => $this->selectedConnection['name']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update SSH connection', [
                'error' => $e->getMessage(),
                'connection_id' => $this->selectedConnection['id']
            ]);
            $this->dispatch('error', message: 'Error updating connection.');
        }
    }

    public function deleteConnection($id)
    {
        try {
            // Delete from database
            SshConnection::findOrFail($id)->delete();

            // Remove from local array
            $this->connections = collect($this->connections)
                ->filter(fn($c) => $c['id'] !== $id)
                ->values()
                ->toArray();

            session()->flash('message', 'Connection deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting connection.');
        }
    }

    public function startConnection($id)
    {
        try {
            Log::info('Starting SSH connection', ['connection_id' => $id]);

            $connection = SshConnection::findOrFail($id);

            Log::info('all', ['connections' => $this->connections]);

            if (!$connection) {
                Log::error('Connection not found', ['connection_id' => $id]);
                throw new \Exception('Connection not found');
            }

            // Update the last_connected_at timestamp
            $sshConnection = SshConnection::findOrFail($id);
            $sshConnection->update(['updated_at' => now()]);

            // Update the connection in the local array
            $this->connections = collect($this->connections)->map(function ($item) use ($sshConnection) {
                if ($item instanceof SshConnection) {
                    $item = $item->toArray();
                }

                if (isset($item['id']) && $item['id'] === $sshConnection->id) {
                    $item['updated_at'] = $sshConnection->updated_at;
                }
                return $item;
            })->toArray();

            // Refresh the menu
            app(MenuController::class)->refreshMenu();

            // Build SSH command with proper script structure
            $scriptContent = "#!/bin/bash\n\n";

            // Clear terminal and add ASCII art
            $scriptContent .= "clear\n";
            $scriptContent .= "cat << 'EOF'\n";
            $scriptContent .= app(ReturnSignature::class)->execute();
            $scriptContent .= "\nEOF\n\n";
            $scriptContent .= "echo \"Connecting to " . $connection['name'] . "...\"\n\n";

            $scriptContent .= "sleep 2\n";
            $scriptContent .= "clear\n";

            // Add SSH command
            $scriptContent .= sprintf(
                'ssh %s@%s -p %d',
                escapeshellarg($connection['username']),
                escapeshellarg($connection['host']),
                $connection['port']
            );
            $scriptContent .= "\n\n";

            // Add self-cleanup command
            $scriptContent .= 'rm -- "$0"';  // This command deletes the script file itself

            // Create a temporary shell script in a more permanent location
            $tempDir = storage_path('app/ssh_scripts');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $filename = 'ssh_' . time() . '_' . uniqid() . '.command';
            $scriptPath = $tempDir . '/' . $filename;

            file_put_contents($scriptPath, $scriptContent);
            chmod($scriptPath, 0755);

            // Open the shell script
            Shell::openFile($scriptPath);

            session()->flash('message', 'Terminal opened with SSH connection.');

            Log::info('SSH connection started successfully', [
                'name' => $connection['name'],
                'host' => $connection['host']
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to start SSH connection', [
                'error' => $e->getMessage(),
                'connection_id' => $id
            ]);
            
            // Replace session flash with dispatch
            $this->dispatch('error', message: 'Failed to open SSH connection: ' . $e->getMessage());
            return;
        }
    }

    private function testConnection($connection)
    {
        Log::info('Testing SSH connection', [
            'connection_id' => $connection['id'],
            'name' => $connection['username'],
            'host' => $connection['host'],
            'port' => $connection['port']
        ]);

        $cacheKey = "ssh_health_{$connection['id']}";

        return Cache::remember($cacheKey, 30, function () use ($connection) {
            $result = SSHHelper::testSSHConnection(
                $connection['host'],
                $connection['username'],
                $connection['password'],
                $connection['port']
            );

            // Log the result
            if ($result === true) {
                Log::info('SSH connection test successful', [
                    'connection_id' => $connection['id'],
                    'name' => $connection['username']
                ]);
            } else {
                Log::warning('SSH connection test failed', [
                    'connection_id' => $connection['id'],
                    'name' => $connection['username'],
                    'error' => $result
                ]);
            }

            return $result === true;
        });
    }

    public function updateConnectionsHealth()
    {
        $this->connections = collect($this->connections)->map(function ($connection) {
            if (is_array($connection)) {
                $connection['is_healthy'] = $this->testConnection($connection);
            } else {
                $connection = $connection->toArray();
                $connection['is_healthy'] = $this->testConnection($connection);
            }
            return $connection;
        })->toArray();
    }

    public function render()
    {
        Log::info('render');
        return view('livewire.ssh-manager');
    }
}
