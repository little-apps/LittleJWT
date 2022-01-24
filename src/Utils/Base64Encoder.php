<?php

namespace LittleApps\LittleJWT\Utils;

use Base64Url\Base64Url;

use Exception;

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
        return Base64Url::encode($data);
    }

    /**
     * Decodes base64 to string
     *
     * @param string $encoded
     * @return string|false Decoded data or false if unable to be decoded.
     */
    public static function decode($encoded)
    {
        try {
            return Base64Url::decode($encoded);
        } catch (Exception $ex) {
            return false;
        }
    }
}
