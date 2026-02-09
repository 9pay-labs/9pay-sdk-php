<?php
declare(strict_types=1);

namespace NinePay\Request;

/**
 * Class PayerAuthRequest
 *
 * Object containing payer authentication request data.
 * @property string requestId
 * @property string currency
 * @property float amount
 * @property array installment
 * @property array card
 * @property string returnUrl
 */
class PayerAuthRequest extends AbstractRequest
{
    /**
     * PayerAuthRequest constructor.
     *
     * @param string $requestId Request ID from Merchant (unique [a-z,A-Z,0-9])
     * @param float $amount Amount to be paid. Min: 3,000,000 vnd
     * @param array $installment Installment info: [amount_original, bank_code, period]
     * @param array $card Card info: [card_number, hold_name, exp_month, exp_year, cvv, buyerPhone (opt), citizenIdentity (opt)]
     * @param string $returnUrl Will return to Merchant site after payer auth finish
     * @param string $currency defaults to 'VND'
     */
    public function __construct(
        string $requestId,
        float $amount,
        array $installment,
        array $card,
        string $returnUrl,
        string $currency = 'VND'
    ) {
        if (empty($requestId) || empty($amount) || empty($installment) || empty($card) || empty($returnUrl)) {
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
        $this->installment = $installment;
        $this->card = $card;
        $this->returnUrl = $returnUrl;
        $this->currency = $currency;
    }

    /**
     * @inheritDoc
     */
    public function toPayload(): array
    {
        return [
            'request_id' => $this->requestId,
            'currency' => $this->currency,
            'amount' => $this->amount,
            'installment' => $this->installment,
            'card' => $this->card,
            'return_url' => $this->returnUrl,
        ];
    }
}
