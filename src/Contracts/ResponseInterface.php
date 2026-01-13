<?php
declare(strict_types=1);

namespace NinePay\Contracts;

/**
 * Interface ResponseInterface
 * 
 * Defines the data structure for a response from the payment gateway.
 */
interface ResponseInterface
{
    /**
     * Check if the request was successful.
     *
     * @return bool
     */
    public function isSuccess(): bool;

    /**
     * Get response data.
     *
     * @return array
     */
    public function getData(): array;

    /**
     * Get message from the response.
     *
     * @return string
     */
    public function getMessage(): string;
}
