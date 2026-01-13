<?php
declare(strict_types=1);

namespace NinePay\Utils;

/**
 * Class Signature
 * 
 * Supports creating and verifying HMAC SHA256 signatures.
 */
final class Signature
{
    /**
     * Create a signature for the message.
     *
     * @param string $message Message to sign.
     * @param string $key Secret key.
     * @return string Base64 encoded signature.
     */
    public static function sign(string $message, string $key): string
    {
        $signature = hash_hmac('sha256', $message, $key, true);
        return base64_encode($signature);
    }

    /**
     * Verify the validity of a signature.
     *
     * @param string $signature Signature to check.
     * @param string $message Original message.
     * @param string $key Secret key.
     * @return bool
     */
    public static function verify(string $signature, string $message, string $key): bool
    {
        $valid = self::sign($message, $key);
        return hash_equals($valid, $signature);
    }
}
