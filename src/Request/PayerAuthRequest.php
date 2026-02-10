<?php
declare(strict_types=1);

namespace NinePay\Request;

use NinePay\Request\Concerns\HasPayerAuthAttributes;

/**
 * Class PayerAuthRequest
 *
 * Object containing payer authentication request data.
 * @property string requestId
 * @property string currency
 * @property float amount
 * @property string returnUrl
 */
class PayerAuthRequest extends AbstractRequest
{
    use HasPayerAuthAttributes;

    /**
     * PayerAuthRequest constructor.
     *
     * @param string $requestId Request ID from Merchant (unique [a-z,A-Z,0-9])
     * @param float $amount Amount to be paid. Min: 3,000,000 vnd
     * @param string $returnUrl Will return to Merchant site after payer auth finish
     * @param string $currency defaults to 'VND'
     */
    public function __construct(
        string $requestId,
        float $amount,
        string $returnUrl,
        string $currency = 'VND'
    ) {
        if (empty($requestId) || empty($amount) || empty($returnUrl)) {
            throw new \InvalidArgumentException('Missing required fields');
        }

        // Validate checks
        if (strlen($requestId) > 30) {
            throw new \InvalidArgumentException('request_id max length is 30');
        }

        if ($amount < 3000000) {
            throw new \InvalidArgumentException('Amount min is 3,000,000');
        }

        $this->requestId = $requestId;
        $this->amount = $amount;
        $this->returnUrl = $returnUrl;
        $this->currency = $currency;
    }
}
