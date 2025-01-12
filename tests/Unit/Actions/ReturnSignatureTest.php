<?php

namespace Tests\Unit\Actions;

use App\Actions\ReturnSignature;
use PHPUnit\Framework\TestCase;

class ReturnSignatureTest extends TestCase
{
    public function test_execute_returns_ascii_art(): void
    {
        $signature = new ReturnSignature();
        $result = $signature->execute();

        $this->assertIsString($result);
        $this->assertStringContainsString('_____', $result);
        $this->assertNotEmpty($result);
    }
} 