<?php
declare(strict_types=1);

namespace NinePay\Contracts;

/**
 * Interface RequestInterface
 * 
 * Defines the data structure for a payment request.
 */
interface RequestInterface
{
    /**
     * Convert the request to a data array.
     *
     * @return array
     */
    public function toArray(): array;
}
