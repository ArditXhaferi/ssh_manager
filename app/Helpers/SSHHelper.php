<?php

namespace App\Helpers;

class SSHHelper
{
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
            // Build SSH test command
            $command = sprintf(
                'ssh -o BatchMode=yes -o ConnectTimeout=5 -o StrictHostKeyChecking=no -p %d %s@%s exit',
                $port,
                escapeshellarg($username),
                escapeshellarg($host)
            );

            // Execute command and capture both output and return value
            $output = [];
            $returnVar = null;
            exec($command . " 2>&1", $output, $returnVar);

            // Check return value (0 means success)
            if ($returnVar === 0) {
                return true;
            }

            // Return error message if connection failed
            return implode("\n", $output);
        } catch (\Exception $e) {
            return "SSH connection error: " . $e->getMessage();
        }
    }
}