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
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodes base64 to string
     *
     * @param string $encoded
     * @return string|false Decoded data or false if unable to be decoded.
     */
    public static function decode($encoded)
    {
        return base64_decode(strtr($encoded, '-_', '+/'), true);
    }
}
