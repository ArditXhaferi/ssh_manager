<?php

namespace Tests\Feature\Livewire;

use App\Livewire\SshKeyManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;

class SshKeyManagerTest extends TestCase
{
    use RefreshDatabase;

    private $testSshDir;
    private $testPublicKey;
    private $testPrivateKey;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test SSH directory
        $this->testSshDir = sys_get_temp_dir() . '/.ssh_test_' . uniqid();
        File::makeDirectory($this->testSshDir, 0700, true);
        
        // Create test keys
        $this->testPublicKey = $this->testSshDir . '/id_test.pub';
        $this->testPrivateKey = $this->testSshDir . '/id_test';
        
        File::put($this->testPublicKey, 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAI... test@example.com');
        File::put($this->testPrivateKey, '-----BEGIN OPENSSH PRIVATE KEY-----...');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->testSshDir);
        parent::tearDown();
    }

    public function test_can_load_public_keys(): void
    {
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $this->testSshDir);
        $component->call('loadPublicKeys');

        $this->assertCount(1, $component->get('publicKeys'));
        $this->assertEquals('id_test.pub', $component->get('publicKeys.0.name'));
    }

    public function test_can_copy_public_key(): void
    {
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $this->testSshDir);
        $component->call('loadPublicKeys');
        
        $component->call('copyPublicKey', 0);
        
        $component->assertDispatched('copyToClipboard', [
            'content' => File::get($this->testPublicKey)
        ]);
    }

    public function test_can_delete_key(): void
    {
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $this->testSshDir);
        $component->call('loadPublicKeys');
        
        $component->call('deleteKey', 0);
        
        $this->assertFalse(File::exists($this->testPublicKey));
        $this->assertFalse(File::exists($this->testPrivateKey));
        $this->assertEmpty($component->get('publicKeys'));
    }

    public function test_can_generate_new_key(): void
    {
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $this->testSshDir);
        
        $component->call('generateNewKey');
        
        // Verify that the script was created
        $scriptFiles = glob(storage_path('app/ssh_scripts/generate_key_*.command'));
        $this->assertNotEmpty($scriptFiles);
        
        // Cleanup
        foreach ($scriptFiles as $file) {
            File::delete($file);
        }
    }

    public function test_handles_missing_ssh_directory(): void
    {
        $nonExistentDir = sys_get_temp_dir() . '/non_existent_ssh_dir';
        
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $nonExistentDir);
        $component->call('loadPublicKeys');
        
        $this->assertEmpty($component->get('publicKeys'));
    }

    public function test_can_open_ssh_directory(): void
    {
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $this->testSshDir);
        $component->call('openSshDirectory');
        
        $this->assertTrue(File::exists($this->testSshDir));
    }

    public function test_mount_sets_correct_ssh_directory(): void
    {
        $component = Livewire::test(SshKeyManager::class);
        
        $expectedDir = match (true) {
            PHP_OS_FAMILY === 'Windows' => getenv('USERPROFILE') . '/.ssh',
            !empty(getenv('HOME')) => getenv('HOME') . '/.ssh',
            default => sys_get_temp_dir() . '/.ssh'
        };
        
        $this->assertEquals($expectedDir, $component->get('sshDirectory'));
    }
} 