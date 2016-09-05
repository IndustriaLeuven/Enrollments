<?php

namespace AppBundle;

final class Util
{
    public static function base64_encode_urlsafe($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    public static function base64_decode_urlsafe($data, $strict = false)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data), $strict);
    }

    public static function shortuuid_encode($uuid)
    {
        return self::base64_encode_urlsafe(hex2bin(str_replace('-', '', $uuid)));
    }

    public static function shortuuid_decode($data)
    {
        $decoded = bin2hex(self::base64_decode_urlsafe($data));
        return substr($decoded, 0, 8).'-'.
                substr($decoded, 8, 4).'-'.
                substr($decoded, 12, 4).'-'.
                substr($decoded, 16, 4).'-'.
                substr($decoded, 20);
    }
}
