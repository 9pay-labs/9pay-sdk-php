<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Utils;

use NinePay\Utils\UnicodeFormat;
use PHPUnit\Framework\TestCase;

class UnicodeFormatTest extends TestCase
{
    public function testUrlsafeB64Decode(): void
    {
        // "test message" in standard base64: dGVzdCBtZXNzYWdl
        // "test message" in urlsafe base64: dGVzdCBtZXNzYWdl (no change for this string)
        $this->assertEquals('test message', UnicodeFormat::urlsafeB64Decode('dGVzdCBtZXNzYWdl'));
        
        // Data that needs padding
        // "test" -> dGVzdA==
        // urlsafe without padding -> dGVzdA
        $this->assertEquals('test', UnicodeFormat::urlsafeB64Decode('dGVzdA'));

        // Data with - and _
        // Standard: + /
        // Urlsafe: - _
        // Example with +/-: binary 11111110 11111111
        // Standard base64 of \xfe\xff: /v8=
        // Urlsafe: _v8
        $this->assertEquals("\xfe\xff", UnicodeFormat::urlsafeB64Decode('_v8'));
    }

    public function testUrlsafeB64DecodeWithEmptyInput(): void
    {
        $this->assertEquals('', UnicodeFormat::urlsafeB64Decode(''));
    }
}
