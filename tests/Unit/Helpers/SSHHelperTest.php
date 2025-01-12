<?php

namespace Tests\Unit\Helpers;

use App\Helpers\SSHHelper;
use PHPUnit\Framework\TestCase;

class SSHHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the global exec function
        if (!function_exists('exec')) {
            eval('namespace App\Helpers; function exec($command, &$output, &$returnVar) { 
                global $mockExecReturnValue;
                $returnVar = $mockExecReturnValue; 
                return ""; 
            }');
        }
    }

    public function test_ssh_connection_test_returns_true_for_valid_connection(): void
    {
        // Mock global return value for the exec function
        global $mockExecReturnValue;
        $mockExecReturnValue = 0;

        // Use Rebex's public SSH test server credentials
        $host = 'test.rebex.net';
        $username = 'demo';
        $password = 'password';
        $port = 22;

        // Perform the SSH connection test
        $result = SSHHelper::testSSHConnection($host, $username, $password, $port);

        // Assert that the connection test returns true
        $this->assertTrue($result, 'The SSH connection test should return true for valid credentials.');
    }

    public function test_ssh_connection_test_returns_false_for_invalid_connection(): void
    {
        global $mockExecReturnValue;
        $mockExecReturnValue = 1;

        $result = SSHHelper::testSSHConnection('invalid-host.com', 'user', null, 22);
        $this->assertIsString($result);
    }
} 