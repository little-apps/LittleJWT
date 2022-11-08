<?php

namespace LittleApps\LittleJWT\Utils;

class Base64Encoder
{
    /**
     * Encodes string to base64
     *
     * @param string $data
     * @return string
     */
    public static function encode($data)
    {
        return rtrim(str_replace(['+', '/'], ['-', '_'], base64_encode($data)), '=');
    }

    /**
     * Decodes base64 to string
     *
     * @param string $encoded
     * @return string|false Decoded data or false if unable to be decoded.
     */
    public static function decode($encoded)
    {
        $decoded = base64_decode(str_replace(['-', '_'], ['+', '/'], $encoded), true);

        return $decoded;
    }
}
