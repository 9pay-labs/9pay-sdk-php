<?php
declare(strict_types=1);

namespace NinePay;

use NinePay\Contracts\PaymentGatewayInterface;
use NinePay\Gateways\NinePayGateway;

/**
 * Class PaymentManager
 * 
 * Manages the initialization and provisioning of payment gateways.
 */
class PaymentManager
{
    /** @var array<string,mixed> Payment system configuration */
    private array $config;
    /** @var PaymentGatewayInterface Payment gateway object */
    private PaymentGatewayInterface $gateway;

    /**
     * PaymentManager constructor.
     *
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->gateway = new NinePayGateway($this->config);
    }

    /**
     * Get the payment gateway instance.
     *
     * @return PaymentGatewayInterface
     */
    public function getGateway(): PaymentGatewayInterface
    {
        return $this->gateway;
    }
}
