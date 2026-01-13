<?php
declare(strict_types=1);

namespace NinePay\Utils;

/**
 * Class UnicodeFormat
 * 
 * Supports encoding and decoding formats.
 */
final class UnicodeFormat
{
    /**
     * Decode URL-safe Base64 string.
     *
     * @param string $input
     * @return string
     */
    public static function urlsafeB64Decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        $decoded = base64_decode(strtr($input, '-_', '+/'));
        return $decoded === false ? '' : $decoded;
    }
}
