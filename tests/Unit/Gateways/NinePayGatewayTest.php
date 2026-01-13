<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Gateways;

use NinePay\Gateways\NinePayGateway;
use NinePay\Support\CreatePaymentRequest;
use NinePay\Utils\HttpClient;
use NinePay\Exceptions\InvalidConfigException;
use PHPUnit\Framework\TestCase;

class NinePayGatewayTest extends TestCase
{
    private array $config = [
        'merchant_id' => 'MID123',
        'secret_key' => 'SECRET',
        'checksum_key' => 'CHECKSUM',
        'env' => 'SANDBOX',
    ];

    public function testConstructorThrowsExceptionOnMissingConfig(): void
    {
        $this->expectException(InvalidConfigException::class);
        new NinePayGateway([]);
    }

    public function testCreatePaymentReturnsRedirectUrl(): void
    {
        $gateway = new NinePayGateway($this->config);
        $request = new CreatePaymentRequest('REQ123', '10000', 'Test');
        
        $response = $gateway->createPayment($request);
        
        $this->assertTrue($response->isSuccess());
        $data = $response->getData();
        $this->assertArrayHasKey('redirect_url', $data);
        $this->assertStringContainsString('sand-payment.9pay.vn/portal', $data['redirect_url']);
        $this->assertStringContainsString('signature=', $data['redirect_url']);
    }

    public function testCreatePaymentFailsOnMissingFields(): void
    {
        $gateway = new NinePayGateway($this->config);
        // CreatePaymentRequest doesn't enforce non-empty in constructor but NinePayGateway checks it
        $request = new CreatePaymentRequest('', '', '');
        
        $response = $gateway->createPayment($request);
        
        $this->assertFalse($response->isSuccess());
        $this->assertStringContainsString('Missing required fields', $response->getMessage());
    }

    public function testVerifyReturnsTrueForValidPayload(): void
    {
        $gateway = new NinePayGateway($this->config);
        $result = 'some-result';
        $checksum = strtoupper(hash('sha256', $result . $this->config['checksum_key']));
        
        $payload = [
            'result' => $result,
            'checksum' => $checksum,
        ];
        
        $this->assertTrue($gateway->verify($payload));
    }

    public function testVerifyReturnsFalseForInvalidPayload(): void
    {
        $gateway = new NinePayGateway($this->config);
        $payload = [
            'result' => 'some-result',
            'checksum' => 'wrong-checksum',
        ];
        
        $this->assertFalse($gateway->verify($payload));
    }

    public function testDecodeResult(): void
    {
        $gateway = new NinePayGateway($this->config);
        // "test" encoded in urlsafe base64 is "dGVzdA"
        $this->assertEquals('test', $gateway->decodeResult('dGVzdA'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInquiry(): void
    {
        $mockHttp = $this->createMock(HttpClient::class);
        $mockHttp->method('get')->willReturn([
            'status' => 200,
            'body' => ['message' => 'Success', 'status' => 'success'],
            'headers' => []
        ]);

        $gateway = new NinePayGateway($this->config, $mockHttp);

         $result = $gateway->inquiry('TRANS123');

         $this->assertTrue($result->isSuccess());
    }
}
