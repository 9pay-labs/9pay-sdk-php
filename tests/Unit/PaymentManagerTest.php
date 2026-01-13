<?php
declare(strict_types=1);

namespace NinePay\Tests\Unit;

use NinePay\PaymentManager;
use NinePay\Gateways\NinePayGateway;
use PHPUnit\Framework\TestCase;

class PaymentManagerTest extends TestCase
{
    private array $config = [
        'merchant_id' => 'MID123',
        'secret_key' => 'SECRET',
        'checksum_key' => 'CHECKSUM',
    ];

    public function testGetGatewayReturnsNinePayGateway(): void
    {
        $manager = new PaymentManager($this->config);
        $gateway = $manager->getGateway();

        $this->assertInstanceOf(NinePayGateway::class, $gateway);
    }
}
