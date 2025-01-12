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
        global $mockExecReturnValue;
        $mockExecReturnValue = 0;

        $result = SSHHelper::testSSHConnection('example.com', 'user', null, 22);
        $this->assertTrue($result);
    }

    public function test_ssh_connection_test_returns_false_for_invalid_connection(): void
    {
        global $mockExecReturnValue;
        $mockExecReturnValue = 1;

        $result = SSHHelper::testSSHConnection('invalid-host.com', 'user', null, 22);
        $this->assertIsString($result);
    }
} 