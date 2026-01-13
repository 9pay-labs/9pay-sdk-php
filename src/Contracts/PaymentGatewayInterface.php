<?php
declare(strict_types=1);

namespace NinePay\Contracts;

/**
 * Interface PaymentGatewayInterface
 * 
 * Defines basic methods for a payment gateway.
 */
interface PaymentGatewayInterface
{
    /**
     * Create a payment request.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function createPayment(RequestInterface $request): ResponseInterface;

    /**
     * Query transaction status.
     *
     * @param string $transactionId
     * @return ResponseInterface
     */
    public function inquiry(string $transactionId): ResponseInterface;

    /**
     * Verify response signature from the payment gateway.
     *
     * @param array $payload
     * @return bool
     */
    public function verify(array $payload): bool;
}
