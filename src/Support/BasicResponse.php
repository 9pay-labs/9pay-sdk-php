<?php
declare(strict_types=1);

namespace NinePay\Support;

use NinePay\Contracts\ResponseInterface;

/**
 * Class BasicResponse
 * 
 * A basic implementation of ResponseInterface.
 */
class BasicResponse implements ResponseInterface
{
    /** @var bool Success status */
    private bool $success;
    /** @var array<string,mixed> Response data */
    private array $data;
    /** @var string Response message */
    private string $message;

    /**
     * BasicResponse constructor.
     *
     * @param bool $success
     * @param array<string,mixed> $data
     * @param string $message
     */
    public function __construct(bool $success, array $data = [], string $message = '')
    {
        $this->success = $success;
        $this->data = $data;
        $this->message = $message;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
