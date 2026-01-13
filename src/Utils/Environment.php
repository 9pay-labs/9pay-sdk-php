<?php
declare(strict_types=1);

namespace NinePay\Utils;

/**
 * Class Environment
 * 
 * Manages API endpoint URLs for different 9Pay environments.
 */
final class Environment
{
    /** @var string Sandbox environment */
    public const SAND = 'https://sand-payment.9pay.vn';
    /** @var string Production environment */
    public const PROD = 'https://payment.9pay.vn';

    /**
     * Get API endpoint URL based on environment name.
     *
     * @param string $env Environment name (SANDBOX, PRODUCTION).
     * @return string Corresponding endpoint URL.
     */
    public static function endpoint(string $env): string
    {
        $map = [
            'SANDBOX' => self::SAND,
            'PRODUCTION' => self::PROD,
        ];
        $key = strtoupper($env);
        return $map[$key] ?? self::SAND;
    }
}
