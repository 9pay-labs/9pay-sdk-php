<?php
declare(strict_types=1);

namespace NinePay\Contracts;

use NinePay\Request\AuthorizeCardPaymentRequest;
use NinePay\Request\CreatePaymentRequest;
use NinePay\Request\CreateRefundRequest;
use NinePay\Request\PayerAuthRequest;

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
     * @param CreatePaymentRequest $request
     * @return ResponseInterface
     */
    public function createPayment(CreatePaymentRequest $request): ResponseInterface;

    /**
     * Query transaction status.
     *
     * @param string $transactionId
     * @return ResponseInterface
     */
    public function inquiry(string $transactionId): ResponseInterface;

    /**
     * Create a refund request.
     *
     * @param CreateRefundRequest $request
     * @return ResponseInterface
     */
    public function refund(CreateRefundRequest $request): ResponseInterface;

    /**
     * Payer authentication request.
     *
     * @param PayerAuthRequest $request
     * @return ResponseInterface
     */
    public function payerAuth(PayerAuthRequest $request): ResponseInterface;

    /**
     * Authorize card payment.
     *
     * @param AuthorizeCardPaymentRequest $request
     * @return ResponseInterface
     */
    public function authorizeCardPayment(AuthorizeCardPaymentRequest $request): ResponseInterface;

    /**
     * Verify response signature from the payment gateway.
     *
     * @param string $result
     * @param string $checksum
     * @return bool
     */
    public function verify(string $result, string $checksum): bool;

    /**
     * Decode result data when verify() is successful.
     *
     * @param string $result Base64 encoded result string.
     * @return string JSON result string after decoding.
     */
    public function decodeResult(string $result): string;
}
