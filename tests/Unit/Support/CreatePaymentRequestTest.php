<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Support;

use NinePay\Support\CreatePaymentRequest;
use PHPUnit\Framework\TestCase;

class CreatePaymentRequestTest extends TestCase
{
    public function testToArray(): void
    {
        $request = new CreatePaymentRequest('REQ123', '10000', 'Test payment');
        $expected = [
            'request_code' => 'REQ123',
            'amount' => '10000',
            'description' => 'Test payment',
        ];
        $this->assertEquals($expected, $request->toArray());
    }

    public function testToArrayWithOptionalFields(): void
    {
        $request = new CreatePaymentRequest(
            'REQ123', 
            '10000', 
            'Test payment',
            'https://example.com/back',
            'https://example.com/return'
        );
        $expected = [
            'request_code' => 'REQ123',
            'amount' => '10000',
            'description' => 'Test payment',
            'back_url' => 'https://example.com/back',
            'return_url' => 'https://example.com/return',
        ];
        $this->assertEquals($expected, $request->toArray());
    }
}
