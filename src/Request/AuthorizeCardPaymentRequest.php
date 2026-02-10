<?php
declare(strict_types=1);

namespace NinePay\Request;

use NinePay\Request\Concerns\HasAuthorizeCardAttributes;

/**
 * Class AuthorizeCardPaymentRequest
 *
 * Object containing card authorization request data.
 * @property string requestId
 * @property int orderCode
 * @property string currency
 * @property float amount
 */
class AuthorizeCardPaymentRequest extends AbstractRequest
{
    use HasAuthorizeCardAttributes;

    /**
     * AuthorizeCardPaymentRequest constructor.
     *
     * @param string $requestId
     * @param int $orderCode
     * @param float $amount
     * @param string $currency
     */
    public function __construct(
        string $requestId,
        int $orderCode,
        float $amount,
        string $currency = 'VND'
    ) {
        if (empty($requestId) || empty($orderCode) || empty($amount) || empty($currency)) {
            throw new \InvalidArgumentException('Missing required fields');
        }

        // Validate checks
        if (strlen($requestId) > 30) {
            throw new \InvalidArgumentException('request_id max length is 30');
        }

        $this->requestId = $requestId;
        $this->orderCode = $orderCode;
        $this->amount = $amount;
        $this->currency = $currency;
    }
}
