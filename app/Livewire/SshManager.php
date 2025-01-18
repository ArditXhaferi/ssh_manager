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
use App\Actions\DispatchNotification;

class SshManager extends Component
{
    public $connections = [];
    public $showNewModal = false;
    public $showEditModal = false;
    public $selectedConnection = null;
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

    protected $rules = [
        'connection.name' => 'required|string|min:3',
        'connection.host' => 'required|string',
        'connection.username' => 'required|string',
        'connection.port' => 'required|integer|min:1|max:65535',
        'connection.password' => 'nullable|string',
        'connection.locked' => 'boolean'
    ];

    public function mount($connections)
    {
        $this->connections = $connections;
        $this->updateConnectionsHealth();
    }

    public function addConnection()
    {
        try {
            // Log all form data before validation
            Log::info('Attempting to create new SSH connection - Form Data:', [
                'name' => $this->connection['name'],
                'host' => $this->connection['host'],
                'username' => $this->connection['username'],
                'port' => $this->connection['port'],
                'locked' => $this->connection['locked'],
                // Don't log password for security reasons
                'has_password' => !empty($this->connection['password'])
            ]);

            $validatedData = $this->validate($this->rules);

            Log::info('Creating new SSH connection', [
                'name' => $this->connection['name'],
                'host' => $this->connection['host'],
                'username' => $this->connection['username'],
                'port' => $this->connection['port']
            ]);

            // Create new SshConnection record in database
            $connection = SshConnection::create([
                'name' => $this->connection['name'],
                'host' => $this->connection['host'],
                'username' => $this->connection['username'],
                'port' => $this->connection['port'],
                'password' => $this->connection['password'],
                'locked' => $this->connection['locked']
            ]);

            // Add the new connection to the local array
            $this->connections[] = $connection->toArray();

            $this->showNewModal = false;
            $this->connection = [
                'name' => '',
                'host' => '',
                'username' => '',
                'port' => 22,
                'password' => '',
                'locked' => false
            ];

            DispatchNotification::execute(
                $this,
                'Connection Added',
                'Successfully added ' . $this->connection['name'],
                'success'
            );

            Log::info('Successfully added new SSH connection', ['name' => $this->connection['name']]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DispatchNotification::execute(
                $this,
                'Validation Failed',
                $e->getMessage(),
                'error'
            );
            throw $e;  // Re-throw the validation exception for Livewire to handle
        } catch (\Exception $e) {
            Log::error('Failed to add SSH connection', [
                'error' => $e->getMessage(),
                'connection' => $this->connection['name']
            ]);
            DispatchNotification::execute(
                $this,
                'Error Adding Connection',
                'Could not add the connection',
                'error'
            );
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
            $connection = SshConnection::findOrFail($this->connection['id']);
            $connection->update([
                'name' => $this->connection['name'],
                'host' => $this->connection['host'],
                'username' => $this->connection['username'],
                'port' => $this->connection['port'],
                'password' => $this->connection['password'],
                'locked' => $this->connection['locked']
            ]);

            // Update the local array
            $this->connections = collect($this->connections)->map(function ($item) {
                if ($item['id'] === $this->connection['id']) {
                    return $this->connection;
                }
                return $item;
            })->toArray();

            $this->showEditModal = false;
            
            DispatchNotification::execute(
                $this,
                'Connection Updated',
                'Successfully updated ' . $this->connection['name'],
                'success'
            );

        } catch (\Exception $e) {
            DispatchNotification::execute(
                $this,
                'Update Failed',
                'Could not update the connection',
                'error'
            );
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
            DispatchNotification::execute(
                $this,
                'Delete Failed',
                'Could not delete the connection',
                'error'
            );
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
            DispatchNotification::execute(
                $this,
                'Failed to open SSH connection',
                'Could not open the SSH connection',
                'error'
            );
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

    public function updatedShowNewModal($value)
    {
        if (!$value) {
            $this->resetValidation();
            $this->connection = [
                'name' => '',
                'host' => '',
                'username' => '',
                'port' => 22,
                'password' => '',
                'locked' => false
            ];
        }
    }

    public function updatedShowEditModal($value)
    {
        if (!$value) {
            $this->resetValidation();
            $this->connection = [
                'name' => '',
                'host' => '',
                'username' => '',
                'port' => 22,
                'password' => '',
                'locked' => false
            ];
        }
    }
}
