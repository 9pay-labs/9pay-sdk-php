<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit\Facades;

use NinePay\Facades\Payment;
use NinePay\Contracts\ResponseInterface;
use NinePay\Support\CreatePaymentRequest;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    private array $config = [
        'merchant_id' => 'MID123',
        'secret_key' => 'SECRET',
        'checksum_key' => 'CHECKSUM',
    ];

    public function testCreatePayment(): void
    {
        $facade = new Payment($this->config);
        $request = new CreatePaymentRequest('REQ123', '1000', 'Desc');
        
        $response = $facade->createPayment($request);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testVerify(): void
    {
        $facade = new Payment($this->config);
        $result = 'test';
        $checksum = strtoupper(hash('sha256', $result . $this->config['checksum_key']));
        
        $this->assertTrue($facade->verify(['result' => $result, 'checksum' => $checksum]));
    }
}
