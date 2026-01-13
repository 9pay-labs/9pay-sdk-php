<?php
declare(strict_types=1);

namespace NinePay\Facades;

use NinePay\Contracts\RequestInterface;
use NinePay\Contracts\ResponseInterface;
use NinePay\Exceptions\PaymentException;
use NinePay\PaymentManager;

/**
 * Class Payment
 * 
 * Facade to simplify the use of NinePay features.
 */
class Payment
{
    /** @var PaymentManager Payment management instance */
    private PaymentManager $manager;

    /**
     * Payment constructor.
     *
     * @param array<string,mixed> $config NinePay configuration.
     */
    public function __construct(array $config)
    {
        $this->manager = new PaymentManager($config);
    }

    /**
     * Create a payment request.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws PaymentException
     */
    public function createPayment(RequestInterface $request): ResponseInterface
    {
        try {
            return $this->manager->getGateway()->createPayment($request);
        } catch (\Throwable $e) {
            throw new PaymentException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Query transaction status.
     *
     * @param string $transactionId
     * @return ResponseInterface
     * @throws PaymentException
     */
    public function inquiry(string $transactionId): ResponseInterface
    {
        try {
            return $this->manager->getGateway()->inquiry($transactionId);
        } catch (\Throwable $e) {
            throw new PaymentException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    /**
     * Verify IPN/Webhook data from 9Pay.
     *
     * @param array<string,mixed> $payload
     * @return bool
     * @throws PaymentException
     */
    public function verify(array $payload): bool
    {
        try {
            return $this->manager->getGateway()->verify($payload);
        } catch (\Throwable $e) {
            throw new PaymentException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}
