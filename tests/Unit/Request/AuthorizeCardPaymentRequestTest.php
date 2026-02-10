<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Request;

use NinePay\Request\AuthorizeCardPaymentRequest;
use PHPUnit\Framework\TestCase;

class AuthorizeCardPaymentRequestTest extends TestCase
{
    public function test_it_can_create_authorize_card_payment_request()
    {
        $request = new AuthorizeCardPaymentRequest(
            'req_123456789',       // Request ID
            436820814786001,     // Order Code
            5000000,               // Amount
        );
        $request->withCard('4456530000001005', 'NGUYEN VAN A', 12, 27, '123');

        $this->assertInstanceOf(AuthorizeCardPaymentRequest::class, $request);
        $payload = $request->toPayload();

        $this->assertEquals('req_123456789', $payload['request_id']);
        $this->assertEquals(436820814786001, $payload['order_code']);
        $this->assertEquals(5000000, $payload['amount']);
        $this->assertEquals('VND', $payload['currency']);

        $this->assertIsArray($payload['card']);
        $this->assertEquals('4456530000001005', $payload['card']['card_number']);
        $this->assertEquals('NGUYEN VAN A', $payload['card']['hold_name']);
        $this->assertEquals(12, $payload['card']['exp_month']);
        $this->assertEquals(25, $payload['card']['exp_year']);
        $this->assertEquals('123', $payload['card']['cvv']);
    }

    public function test_it_throws_exception_if_request_id_too_long()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('request_id max length is 30');

        new AuthorizeCardPaymentRequest(
            'this_request_id_is_way_too_long_to_be_accepted' . time(),       // Request ID
            436820814786001,     // Order Code
            5000000,               // Amount
        );
    }

    public function test_it_throws_exception_if_missing_required_fields()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields');

        new AuthorizeCardPaymentRequest(
            'this_request_id_is_way_too_long_to_be_accepted' . time(),       // Request ID
            436820814786001,     // Order Code
            5000000,               // Amount
        );
    }
}
