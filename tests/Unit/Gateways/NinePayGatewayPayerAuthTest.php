<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Gateways;

use NinePay\Config\NinePayConfig;
use NinePay\Gateways\NinePayGateway;
use NinePay\Request\PayerAuthRequest;
use PHPUnit\Framework\TestCase;
use NinePay\Utils\HttpClient;

class NinePayGatewayPayerAuthTest extends TestCase
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

    public function test_payer_auth_success()
    {
        $request = new PayerAuthRequest(
            'req_123',
            5000000,
            ['period' => 12],
            ['card_number' => '123'],
            'https://callback.url'
        );

        $this->http->expects($this->once())
            ->method('post')
            ->with(
                $this->stringContains('/v2/payments/payer-auth'),
                $this->callback(function ($payload) {
                    return $payload['request_id'] === 'req_123' &&
                           $payload['amount'] == 5000000;
                }),
                $this->callback(function ($headers) {
                    return isset($headers['Authorization']) &&
                           strpos($headers['Authorization'], 'Signature Algorithm=HS256') !== false;
                })
            )
            ->willReturn([
                'status' => 200,
                'body' => ['status' => 'success', 'message' => 'Auth successful']
            ]);

        $response = $this->gateway->payerAuth($request);
        $this->assertTrue($response->isSuccess());
        $this->assertEquals('Auth successful', $response->getMessage());
    }

    public function test_payer_auth_failure()
    {
        $request = new PayerAuthRequest(
            'req_123',
            5000000,
            ['period' => 12],
            ['card_number' => '123'],
            'https://callback.url'
        );

        $this->http->expects($this->once())
            ->method('post')
            ->willReturn([
                'status' => 400,
                'body' => ['message' => 'Invalid card']
            ]);

        $response = $this->gateway->payerAuth($request);
        $this->assertFalse($response->isSuccess());
        $this->assertEquals('Invalid card', $response->getMessage());
    }
}
