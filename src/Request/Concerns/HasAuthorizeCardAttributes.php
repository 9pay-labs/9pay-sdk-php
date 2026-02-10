<?php
declare(strict_types=1);

namespace NinePay\Request\Concerns;

/**
 * @property array card
 */
trait HasAuthorizeCardAttributes
{
    public function withCard(string $cardNumber, string $holdName, int $expMonth, int $expYear, string $cvv): self
    {
        $this->card = [
            'card_number' => $cardNumber,
            'hold_name' => $holdName,
            'exp_month' => $expMonth,
            'exp_year' => $expYear,
            'cvv' => $cvv
        ];

        return $this;
    }
}
