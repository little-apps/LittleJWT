<?php

namespace LittleApps\LittleJWT\Utils;

use Exception;
use InvalidArgumentException;
use Jose\Component\Core\Util\JsonConverter as JoseJsonConverter;
use LittleApps\LittleJWT\Exceptions\InvalidClaimValueException;

class JsonEncoder
{
    /**
     * Encodes an array as JSON.
     *
     * @param  array  $data
     * @return string
     */
    public static function encode($data)
    {
        try {
            return JoseJsonConverter::encode($data);
        } catch (InvalidArgumentException $ex) {
            throw new InvalidClaimValueException($data, $ex);
        }
    }

    /**
     * Decodes a JSON string to an array
     *
     * @param  string  $encoded
     * @return array|null Returns an array or null if unable to be decoded.
     */
    public static function decode($encoded)
    {
        try {
            $ret = JoseJsonConverter::decode($encoded);

            return is_array($ret) ? $ret : null;
        } catch (Exception $ex) {
            return null;
        }
    }
}
