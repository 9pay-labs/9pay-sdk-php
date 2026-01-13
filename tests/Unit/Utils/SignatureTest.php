<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Utils;

use NinePay\Utils\Signature;
use PHPUnit\Framework\TestCase;

class SignatureTest extends TestCase
{
    private string $key = 'secret';
    private string $message = 'hello world';

    public function testSign(): void
    {
        $signature = Signature::sign($this->message, $this->key);
        $this->assertNotEmpty($signature);
        $this->assertEquals(base64_encode(hash_hmac('sha256', $this->message, $this->key, true)), $signature);
    }

    public function testVerifyReturnsTrueForValidSignature(): void
    {
        $signature = Signature::sign($this->message, $this->key);
        $this->assertTrue(Signature::verify($signature, $this->message, $this->key));
    }

    public function testVerifyReturnsFalseForInvalidSignature(): void
    {
        $this->assertFalse(Signature::verify('invalid-signature', $this->message, $this->key));
    }

    public function testVerifyReturnsFalseForDifferentMessage(): void
    {
        $signature = Signature::sign($this->message, $this->key);
        $this->assertFalse(Signature::verify($signature, 'different message', $this->key));
    }
}
