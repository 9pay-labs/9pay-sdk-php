<?php
declare(strict_types=1);

namespace NinePay\Request\Concerns;

use InvalidArgumentException;
use NinePay\Enums\Currency;

/**
 * @property string|null currency
 * @property string|null bank
 * @property string|null account_number
 * @property string|null customer_name
 */
trait HasRefundAttributes
{
    public function withCurrency(string $currency): self
    {
        if (!Currency::isValid($currency)) {
            throw new InvalidArgumentException("Invalid currency: $currency");
        }
        $this->currency = $currency;
        return $this;
    }

    public function withBank(string $bank, string $accountNo, string $accountName): self
    {
        $this->bank = $bank;
        $this->account_number = $accountNo;
        $this->customer_name = $accountName;

        return $this;
    }
}
