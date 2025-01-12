<?php

namespace App\Helpers;

class SSHHelper
{
    /**
     * Get base SSH options
     *
     * @return string
     */
    private static function getBaseSSHOptions(): string
    {
        return '-o BatchMode=no -o ConnectTimeout=5 -o StrictHostKeyChecking=no -o PreferredAuthentications=password,keyboard-interactive';
    }

    /**
     * Create expect script for password authentication
     *
     * @param string $sshOptions
     * @param int $port
     * @param string $username
     * @param string $host
     * @param string $password
     * @return string
     */
    private static function createExpectScript($sshOptions, $port, $username, $host, $password): string
    {
        return sprintf('expect << EOF
            spawn ssh %s -p %d %s@%s exit
            expect {
                "password:" {
                    send "%s\r"
                    expect eof
                }
                timeout {
                    exit 1
                }
            }
            catch wait result
            exit [lindex \$result 3]
EOF', $sshOptions, $port, $username, $host, $password);
    }

    /**
     * Create regular SSH command for key-based authentication
     *
     * @param string $sshOptions
     * @param int $port
     * @param string $username
     * @param string $host
     * @return string
     */
    private static function createSSHCommand($sshOptions, $port, $username, $host): string
    {
        return sprintf(
            'ssh %s -p %d %s@%s exit',
            $sshOptions,
            $port,
            escapeshellarg($username),
            escapeshellarg($host)
        );
    }

    /**
     * Execute command and process results
     *
     * @param string $command
     * @return bool|string
     */
    private static function executeCommand($command)
    {
        exec($command . " 2>&1", $output, $returnVar);
        
        if ($returnVar === 0) {
            return true;
        }

        return is_array($output) ? implode("\n", $output) : $output;
    }

    /**
     * Test if SSH connection can be established with the given credentials.
     *
     * @param string $host
     * @param string $username
     * @param string|null $password
     * @param int $port
     * @return bool|string Returns true if connection successful, error message string if failed
     */
    public static function testSSHConnection($host, $username, $password = null, $port = 22)
    {
        try {
            $sshOptions = self::getBaseSSHOptions();
            
            if ($password !== null) {
                $command = self::createExpectScript($sshOptions, $port, $username, $host, $password);
            } else {
                $command = self::createSSHCommand($sshOptions, $port, $username, $host);
            }

            return self::executeCommand($command);
        } catch (\Exception $e) {
            return "SSH connection error: " . $e->getMessage();
        }
    }
}