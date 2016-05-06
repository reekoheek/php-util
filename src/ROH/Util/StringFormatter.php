<?php
namespace ROH\Util;

use ArrayAccess;
use Exception;

class StringFormatter
{
    public static function format($format, $data = [])
    {
        if (static::isStatic($format)) {
            return $format;
        } elseif (is_array($data)) {
            $replace = array();
            foreach ($data as $key => $val) {
                $replace['{' . $key . '}'] = $val;
            }
            return strtr($format, $replace);
        } elseif ($data instanceof ArrayAccess) {
            return preg_replace_callback('/{([^}]+)}/', function ($matches) use ($data) {
                return $data[$matches[1]];
            }, $format);
        } else {
            throw new Exception('Unsuitable data format');
        }
    }

    public static function isStatic($format)
    {
        return strpos($format, '{') === false;
    }
}
