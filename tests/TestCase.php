<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Native\Laravel\Shell as NativeShell;
use Native\Laravel\Facades\Shell;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test shell instance with mock client
        $shell = new class(new \Native\Laravel\Client\Client) extends NativeShell {
            public function openFile(string $path): string
            {
                return $path;
            }
            
            public function exec(string $command): string
            {
                return '';
            }
        };
        
        // Bind the test instance to the container
        $this->app->instance(NativeShell::class, $shell);
    }
}
