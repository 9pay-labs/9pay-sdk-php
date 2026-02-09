<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Request;

use NinePay\Request\PayerAuthRequest;
use PHPUnit\Framework\TestCase;

class PayerAuthRequestTest extends TestCase
{
    public function test_it_can_create_payer_auth_request()
    {
        $request = new PayerAuthRequest(
            'req_123456789',
            5000000,
            [
                'amount_original' => 5000000,
                'bank_code' => 'VCB',
                'period' => 12
            ],
            [
                'card_number' => '1234567890123456',
                'hold_name' => 'NGUYEN VAN A',
                'exp_month' => '12',
                'exp_year' => '25',
                'cvv' => '123'
            ],
            'https://merchant.com/callback'
        );

        $this->assertInstanceOf(PayerAuthRequest::class, $request);
        $payload = $request->toPayload();

        $this->assertEquals('req_123456789', $payload['request_id']);
        $this->assertEquals(5000000, $payload['amount']);
        $this->assertEquals('VND', $payload['currency']);
        $this->assertEquals('https://merchant.com/callback', $payload['return_url']);
        $this->assertIsArray($payload['installment']);
        $this->assertIsArray($payload['card']);
    }

    public function test_it_throws_exception_if_request_id_too_long()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('request_id max length is 30');

        new PayerAuthRequest(
            'this_request_id_is_way_too_long_to_be_accepted',
            5000000,
            ['period' => 12],
            ['card_number' => '123'],
            'url'
        );
    }

    public function test_it_throws_exception_if_amount_too_small()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount min is 3,000,000');

        new PayerAuthRequest(
            'req_123',
            2000000,
            ['period' => 12],
            ['card_number' => '123'],
            'url'
        );
    }
}
