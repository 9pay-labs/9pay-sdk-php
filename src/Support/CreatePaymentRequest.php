<?php
declare(strict_types=1);

namespace NinePay\Support;

use NinePay\Contracts\RequestInterface;

/**
 * Class CreatePaymentRequest
 * 
 * Object containing payment creation request data.
 */
class CreatePaymentRequest implements RequestInterface
{
    /** @var string Request code (unique for each transaction) */
    private string $requestCode;
    /** @var string Payment amount */
    private string $amount;
    /** @var string Transaction description */
    private string $description;
    /** @var string|null URL to return to after payment is completed */
    private ?string $backUrl;
    /** @var string|null URL to receive response from 9Pay */
    private ?string $returnUrl;

    /**
     * CreatePaymentRequest constructor.
     *
     * @param string $requestCode
     * @param string $amount
     * @param string $description
     * @param string|null $backUrl
     * @param string|null $returnUrl
     */
    public function __construct(
        string $requestCode,
        string $amount,
        string $description,
        ?string $backUrl = null,
        ?string $returnUrl = null
    ) {
        $this->requestCode = $requestCode;
        $this->amount = $amount;
        $this->description = $description;
        $this->backUrl = $backUrl;
        $this->returnUrl = $returnUrl;
    }

    public function toArray(): array
    {
        $data = [
            'request_code' => $this->requestCode,
            'amount' => $this->amount,
            'description' => $this->description,
        ];
        if (!empty($this->backUrl)) {
            $data['back_url'] = (string)$this->backUrl;
        }
        if (!empty($this->returnUrl)) {
            $data['return_url'] = (string)$this->returnUrl;
        }
        return $data;
    }
}
