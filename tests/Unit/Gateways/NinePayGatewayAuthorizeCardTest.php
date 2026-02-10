<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Gateways;

use NinePay\Config\NinePayConfig;
use NinePay\Gateways\NinePayGateway;
use NinePay\Request\AuthorizeCardPaymentRequest;
use PHPUnit\Framework\TestCase;
use NinePay\Utils\HttpClient;

class NinePayGatewayAuthorizeCardTest extends TestCase
{
    private $config;
    private $http;
    private $gateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->config = new NinePayConfig('merchant', 'secret', 'checksum');
        $this->http = $this->createMock(HttpClient::class);
        $this->gateway = new NinePayGateway($this->config, $this->http);
    }

    public function test_authorize_card_success()
    {
        $request = new AuthorizeCardPaymentRequest(
            'req_123',
            'order_123',
            5000000,
            '1234567890123456',
            'NGUYEN VAN A',
            '12',
            '25',
            '123'
        );

        $this->http->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('/v2/payments/authorize'),
                $this->callback(function ($payload) {
                    return $payload['request_id'] === 'req_123' &&
                           $payload['order_code'] === 'order_123' &&
                           $payload['amount'] == 5000000 &&
                           isset($payload['card']);
                }),
                $this->callback(function ($headers) {
                    return isset($headers['Authorization']) &&
                           strpos($headers['Authorization'], 'Signature Algorithm=HS256') !== false;
                })
            )
            ->willReturn([
                'status' => 200,
                'body' => [
                    'status' => 'success',
                    'message' => 'Authorization successful',
                    'data' => ['transaction_id' => 'trans_123']
                ]
            ]);

        $response = $this->gateway->authorizeCardPayment($request);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('Authorization successful', $response->getMessage());
        $this->assertEquals('trans_123', $response->getData()['data']['transaction_id']);
    }

    public function test_authorize_card_failure()
    {
        $request = new AuthorizeCardPaymentRequest(
            'req_123',
            'order_123',
            5000000,
            '1234567890123456',
            'NGUYEN VAN A',
            '12',
            '25',
            '123'
        );

        $this->http->expects($this->once())
            ->method('post')
            ->willReturn([
                'status' => 400,
                'body' => ['message' => 'Invalid card details']
            ]);

        $response = $this->gateway->authorizeCardPayment($request);
        $this->assertFalse($response->isSuccess());
        $this->assertEquals('Invalid card details', $response->getMessage());
    }
}
