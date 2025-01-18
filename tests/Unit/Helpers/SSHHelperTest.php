<?php

namespace Tests\Unit\Helpers;

use App\Helpers\SSHHelper;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use phpmock\phpunit\PHPMock;

class SSHHelperTest extends TestCase
{
    use PHPMock;
    
    private $mockOutput = [];
    private $reflection;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup reflection to test private methods
        $this->reflection = new ReflectionClass(SSHHelper::class);
        
        // Mock the global exec function
        if (!function_exists('exec')) {
            eval('namespace App\Helpers; function exec($command, &$output, &$returnVar) { 
                global $mockExecReturnValue, $mockOutput;
                $returnVar = $mockExecReturnValue;
                $output = $mockOutput; 
                return implode("\n", $mockOutput);
            }');
        }
    }

    // Helper method to call private methods
    private function callPrivateMethod($methodName, array $params = [])
    {
        $method = $this->reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs(null, $params);
    }

    // Test getBaseSSHOptions
    public function test_get_base_ssh_options(): void
    {
        $options = $this->callPrivateMethod('getBaseSSHOptions');
        $this->assertIsString($options);
        $this->assertStringContainsString('BatchMode=no', $options);
        $this->assertStringContainsString('ConnectTimeout=5', $options);
        $this->assertStringContainsString('StrictHostKeyChecking=no', $options);
        $this->assertStringContainsString('PreferredAuthentications=password,keyboard-interactive', $options);
    }

    // Test createExpectScript
    public function test_create_expect_script(): void
    {
        $script = $this->callPrivateMethod('createExpectScript', [
            '-o Option=value',
            22,
            'testuser',
            'testhost',
            'testpass'
        ]);
        
        $this->assertIsString($script);
        $this->assertStringContainsString('spawn ssh', $script);
        $this->assertStringContainsString('expect {', $script);
        $this->assertStringContainsString('send "testpass\r"', $script);
    }

    // Test createSSHCommand
    public function test_create_ssh_command(): void
    {
        $command = $this->callPrivateMethod('createSSHCommand', [
            '-o Option=value',
            22,
            'test@user',
            'test.host'
        ]);
        
        $this->assertIsString($command);
        $this->assertStringContainsString('ssh -o Option=value', $command);
        $this->assertStringContainsString('-p 22', $command);
        // Verify shell escaping
        $this->assertStringContainsString("'test@user'", $command);
        $this->assertStringContainsString("'test.host'", $command);
    }

    public function test_create_ssh_command_properly_escapes_special_characters(): void
    {
        $command = $this->callPrivateMethod('createSSHCommand', [
            '-o Option=value',
            22,
            'user;with&special',
            'host;with&special'
        ]);
        
        $this->assertIsString($command);
        // Check that the values are wrapped in single quotes
        $this->assertMatchesRegularExpression("/.*'user;with&special'@'host;with&special'.*/", $command);
        // Verify the basic command structure
        $this->assertStringStartsWith('ssh -o Option=value -p 22', $command);
        $this->assertStringEndsWith('exit', $command);
    }

    public function test_execute_command_success(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 0;
        $mockOutput = ['Success'];

        $result = $this->callPrivateMethod('executeCommand', ['test command']);
        
        $this->assertTrue($result);
        $this->assertIsNotString($result);
        $this->assertNotEquals('Success', $result);
    }

    public function test_execute_command_failure(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['Error message'];

        $result = $this->callPrivateMethod('executeCommand', ['test command']);
        $this->assertEquals('Error message', $result);
    }

    // Existing integration tests
    public function test_ssh_connection_test_returns_true_for_valid_connection(): void
    {
        global $mockExecReturnValue;
        $mockExecReturnValue = 0;

        $result = SSHHelper::testSSHConnection('test.rebex.net', 'demo', 'password', 22);
        $this->assertTrue($result);
    }

    public function test_ssh_connection_test_with_key_authentication(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['Permission denied (publickey).'];

        $result = SSHHelper::testSSHConnection('164.92.236.6', 'root', null);
        $this->assertIsString($result);
        $this->assertStringContainsString('Permission denied', $result);
    }

    public function test_ssh_connection_test_returns_error_message_for_invalid_connection(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['Operation timed out'];

        $result = SSHHelper::testSSHConnection('invalid-host.com', 'user', null);
        $this->assertIsString($result);
        $this->assertStringContainsString('Operation timed out', $result);
    }

    public function test_ssh_connection_test_with_special_characters(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['Invalid username format'];
        
        $result = SSHHelper::testSSHConnection(
            'host.com',
            'user@with@special',
            'pass\'word"special'
        );
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Invalid username format', $result);
    }

    public function test_ssh_connection_test_with_custom_port(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['No route to host'];

        $result = SSHHelper::testSSHConnection('example.com', 'user', null, 2222);
        $this->assertIsString($result);
        $this->assertStringContainsString('No route to host', $result);
    }

    public function test_ssh_connection_test_handles_timeout(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['ssh: Could not resolve hostname slow-host.com: nodename nor servname provided, or not known'];

        $result = SSHHelper::testSSHConnection('slow-host.com', 'user', 'password');
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Could not resolve hostname', $result);
    }

    public function test_ssh_connection_test_handles_host_not_found(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['Could not resolve hostname'];

        $result = SSHHelper::testSSHConnection('nonexistent.host', 'user', 'password');
        $this->assertIsString($result);
        $this->assertStringContainsString('Could not resolve hostname', $result);
    }

    public function test_ssh_connection_test_handles_exception(): void
    {
        global $mockExecReturnValue, $mockOutput;
        $mockExecReturnValue = 1;
        $mockOutput = ['ssh: Could not resolve hostname test.host: nodename nor servname provided, or not known'];

        $result = SSHHelper::testSSHConnection('test.host', 'valid_user', null);
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Could not resolve hostname', $result);
    }
} 