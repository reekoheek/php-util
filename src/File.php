<?php

namespace ROH\Util;

class File
{
    public static function rm($path)
    {
        if (is_dir($path)) {
            foreach (glob($path . '/*') as $file) {
                static::rm($file);
            }
            @rmdir($path);
        } else {
            @unlink($path);
        }
    }
}
