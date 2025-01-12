<?php

namespace Tests\Unit\Helpers;

use App\Helpers\SSHHelper;
use PHPUnit\Framework\TestCase;

class SSHHelperTest extends TestCase
{
    private $mockOutput = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the global exec function with more detailed behavior
        if (!function_exists('exec')) {
            eval('namespace App\Helpers; function exec($command, &$output, &$returnVar) { 
                global $mockExecReturnValue, $mockOutput;
                $returnVar = $mockExecReturnValue;
                $output = $mockOutput; 
                return implode("\n", $mockOutput);
            }');
        }
    }

    public function test_ssh_connection_test_returns_true_for_valid_connection(): void
    {
        global $mockExecReturnValue;
        $mockExecReturnValue = 0;

        $result = SSHHelper::testSSHConnection('test.rebex.net', 'demo', 'password', 22);
        $this->assertTrue($result);
    }

    public function test_ssh_connection_test_with_password_authentication(): void
    {
        global $mockExecReturnValue;
        $mockExecReturnValue = 0;

        $result = SSHHelper::testSSHConnection('example.com', 'user', 'password');
        $this->assertTrue($result);
    }

    public function test_ssh_connection_test_with_key_authentication(): void
    {
        global $mockExecReturnValue;
        $mockExecReturnValue = 0;

        $result = SSHHelper::testSSHConnection('example.com', 'user', null);
        $this->assertTrue($result);
    }

    public function test_ssh_connection_test_returns_error_message_for_invalid_connection(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['Permission denied (publickey,password)'];

        $result = SSHHelper::testSSHConnection('invalid-host.com', 'user', null);
        $this->assertIsString($result);
        $this->assertStringContainsString('Permission denied', $result);
    }

    public function test_ssh_connection_test_with_special_characters(): void
    {
        global $mockExecReturnValue;
        $mockExecReturnValue = 0;

        $result = SSHHelper::testSSHConnection(
            'host.com', 
            'user@with@special', 
            'pass\'word"special'
        );
        $this->assertTrue($result);
    }

    public function test_ssh_connection_test_with_custom_port(): void
    {
        global $mockExecReturnValue;
        $mockExecReturnValue = 0;

        $result = SSHHelper::testSSHConnection('example.com', 'user', null, 2222);
        $this->assertTrue($result);
    }

    public function test_ssh_connection_test_handles_timeout(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['Connection timed out'];

        $result = SSHHelper::testSSHConnection('slow-host.com', 'user', 'password');
        $this->assertIsString($result);
        $this->assertStringContainsString('Connection timed out', $result);
    }

    public function test_ssh_connection_test_handles_host_not_found(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['No such host is known'];

        $result = SSHHelper::testSSHConnection('nonexistent.host', 'user', 'password');
        $this->assertIsString($result);
        $this->assertStringContainsString('No such host is known', $result);
    }

    public function test_ssh_connection_test_handles_exception(): void
    {
        global $mockExecReturnValue;
        $mockExecReturnValue = 255;

        $result = SSHHelper::testSSHConnection(null, null, null);
        $this->assertIsString($result);
        $this->assertStringContainsString('SSH connection error', $result);
    }
} 