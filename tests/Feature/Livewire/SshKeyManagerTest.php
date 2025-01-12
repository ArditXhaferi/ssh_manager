<?php

namespace Tests\Feature\Livewire;

use App\Livewire\SshKeyManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Livewire\Livewire;
use Tests\TestCase;
use Native\Laravel\Facades\Shell;

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
        $this->assertArrayHasKey('content', $component->get('publicKeys.0'));
        $this->assertArrayHasKey('path', $component->get('publicKeys.0'));
    }

    public function test_can_copy_public_key(): void
    {
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $this->testSshDir);
        $component->call('loadPublicKeys');
    
        $publicKeyContent = File::get($this->testPublicKey);
        
        $component->call('copyPublicKey', 0)
            ->assertDispatched('copyToClipboard', content: $publicKeyContent);
    }

    public function test_copy_public_key_handles_invalid_index(): void
    {
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $this->testSshDir);
        
        $component->call('copyPublicKey', 999)
            ->assertNotDispatched('copyToClipboard');
    }

    public function test_can_generate_new_key(): void
    {
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $this->testSshDir);
        
        $component->call('generateNewKey');
        
        // Verify that the script was created
        $scriptFiles = glob(storage_path('app/ssh_scripts/generate_key_*.command'));
        $this->assertNotEmpty($scriptFiles);
        
        // Verify script content
        $scriptContent = File::get($scriptFiles[0]);
        $this->assertStringContainsString('#!/bin/bash', $scriptContent);
        $this->assertStringContainsString('ssh-keygen', $scriptContent);
        $this->assertStringContainsString('Enter key name', $scriptContent);
        
        // Cleanup
        foreach ($scriptFiles as $file) {
            File::delete($file);
        }
    }

    public function test_generate_new_key_creates_directory_if_not_exists(): void
    {
        $newSshDir = sys_get_temp_dir() . '/.ssh_new_' . uniqid();
        
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $newSshDir);
        
        $component->call('generateNewKey');
        
        $this->assertTrue(File::exists($newSshDir));
        $this->assertEquals(0700, octdec(substr(sprintf('%o', fileperms($newSshDir)), -4)));
        
        // Cleanup
        File::deleteDirectory($newSshDir);
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
        // Clear any existing mocks
        \Mockery::close();
        
        Shell::partialMock()
            ->shouldReceive('openFile')
            ->once()
            ->with($this->testSshDir);
        
        $component = Livewire::test(SshKeyManager::class);
        $component->set('sshDirectory', $this->testSshDir);
        $component->call('openSshDirectory');
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

    public function test_generate_new_key_handles_script_creation_failure(): void
    {
        // Make storage directory read-only
        $storageDir = storage_path('app/ssh_scripts');
        if (!File::exists($storageDir)) {
            File::makeDirectory($storageDir, 0400, true);
        }
        chmod($storageDir, 0400);

        $component = Livewire::test(SshKeyManager::class);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to generate SSH key');
        
        $component->call('generateNewKey');

        // Cleanup
        chmod($storageDir, 0755);
    }
} 