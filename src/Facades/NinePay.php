<?php

namespace NinePay\Facades;

use Illuminate\Support\Facades\Facade;
use NinePay\Contracts\PaymentGatewayInterface;
use NinePay\Contracts\ResponseInterface;
use NinePay\Request\CreatePaymentRequest;
use NinePay\Request\CreateRefundRequest;

/**
 * @method static ResponseInterface createPayment(CreatePaymentRequest $request)
 * @method static ResponseInterface inquiry(string $transactionId)
 * @method static ResponseInterface refund(CreateRefundRequest $request)
 * @method static bool verify(string $result, string $checksum)
 * @method static string decodeResult()
 *
 * @see PaymentGatewayInterface
 */
class NinePay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ninepay';
    }
}
