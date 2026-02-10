<?php
declare(strict_types=1);

namespace NinePay\Request\Concerns;

/**
 * @property array installment
 * @property array card
 */
trait HasPayerAuthAttributes
{
    /**
     * @param float $amount
     * @param string $bankCode
     * @param int $period Payment term 3, 6, 9, 12
     * @return HasPayerAuthAttributes
     */
    public function withInstallment(float $amount, string $bankCode, int $period = 12): self
    {
        $this->installment = [
            'amount_original' => $amount,
            'bank_code' => $bankCode,
            'period' => $period
        ];

        return $this;
    }

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
