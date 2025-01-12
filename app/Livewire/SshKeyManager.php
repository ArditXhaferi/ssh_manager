<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\File;
use Native\Laravel\Facades\Shell;
use App\Actions\ReturnSignature;

class SshKeyManager extends Component
{
    public $publicKeys = [];
    public $sshDirectory;

    public function mount()
    {
        $this->sshDirectory = match (true) {
            function_exists('posix_getpwuid') && function_exists('posix_getuid') =>
            (posix_getpwuid(posix_getuid())['dir'] ?? null) . '/.ssh',
            !empty($_SERVER['HOME']) => $_SERVER['HOME'] . '/.ssh',
            !empty(getenv('HOME')) => getenv('HOME') . '/.ssh',
            PHP_OS_FAMILY === 'Windows' => getenv('USERPROFILE') . '/.ssh',
            default => sys_get_temp_dir() . '/.ssh'
        };

        $this->loadPublicKeys();
    }

    public function loadPublicKeys()
    {
        if (!File::exists($this->sshDirectory)) {
            return;
        }

        $files = File::files($this->sshDirectory);
        $this->publicKeys = collect($files)
            ->filter(function ($file) {
                return str_ends_with($file->getFilename(), '.pub');
            })
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'content' => File::get($file->getPathname()),
                ];
            })
            ->values()
            ->toArray();
    }

    public function copyPublicKey($index)
    {
        if (isset($this->publicKeys[$index])) {
            $this->dispatch('copyToClipboard', content: $this->publicKeys[$index]['content']);
        }
    }

    public function openSshDirectory()
    {
        if (!File::exists($this->sshDirectory)) {
            File::makeDirectory($this->sshDirectory, 0700, true);
        }

        Shell::openFile($this->sshDirectory);
    }

    public function generateNewKey()
    {
        if (!File::exists($this->sshDirectory)) {
            File::makeDirectory($this->sshDirectory, 0700, true);
        }

        try {
            // Create a temporary shell script with user prompts
            $scriptContent = "#!/bin/bash\n\n";

            // Add ASCII art using the ReturnSignature action
            $scriptContent .= "cat << 'EOF'\n";
            $scriptContent .= app(ReturnSignature::class)->execute();
            $scriptContent .= "\nEOF\n\n";

            $scriptContent .= "echo 'SSH Key Generator'\n";
            $scriptContent .= "echo '----------------'\n\n";

            // Get key name
            $scriptContent .= "echo -n 'Enter key name (default: id_ed25519): '\n";
            $scriptContent .= "read -r keyname\n";
            $scriptContent .= "keyname=\${keyname:-id_ed25519}\n\n";

            // Get email/comment
            $scriptContent .= "echo -n 'Enter email or comment for the key: '\n";
            $scriptContent .= "read -r comment\n";
            $scriptContent .= "comment=\${comment:-\"" . gethostname() . "\"}\n\n";

            // Get passphrase (optional)
            $scriptContent .= "echo -n 'Enter passphrase (empty for no passphrase): '\n";
            $scriptContent .= "read -r -s passphrase\n";
            $scriptContent .= "echo\n\n";

            // Generate the key
            $scriptContent .= "echo 'Generating SSH key...'\n";
            $scriptContent .= sprintf(
                "ssh-keygen -t ed25519 -C \"\$comment\" -f \"%s/\$keyname\" \${passphrase:+-N \"\$passphrase\"}\n",
                $this->sshDirectory
            );

            // Show the public key
            $scriptContent .= "echo\necho 'Your public key:'\n";
            $scriptContent .= "echo '-------------'\n";
            $scriptContent .= "cat \"\$HOME/.ssh/\$keyname.pub\"\n";
            $scriptContent .= "echo\necho 'Press any key to close...'\n";
            $scriptContent .= "read -n 1\n";

            // Create script directory if it doesn't exist
            $tempDir = storage_path('app/ssh_scripts');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Create and save the script
            $scriptPath = $tempDir . '/generate_key_' . time() . '.command';
            file_put_contents($scriptPath, $scriptContent);
            chmod($scriptPath, 0755);

            // Open the script in terminal
            Shell::openFile($scriptPath);

            // Wait a bit longer since we're waiting for user input
            sleep(3);

            $this->loadPublicKeys();
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to generate SSH key: ' . $e->getMessage());
        }
    }

    public function deleteKey($index)
    {
        if (isset($this->publicKeys[$index])) {
            $publicKeyPath = $this->publicKeys[$index]['path'];
            $privateKeyPath = str_replace('.pub', '', $publicKeyPath);

            // Delete private key if it exists
            if (File::exists($privateKeyPath)) {
                File::delete($privateKeyPath);
            }

            // Delete public key
            File::delete($publicKeyPath);

            // Reload the keys
            $this->loadPublicKeys();
        }
    }

    public function render()
    {
        return view('livewire.ssh-key-manager');
    }
}
