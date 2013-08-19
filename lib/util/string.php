<?php
/**
 * file: string.php
 * Author: wuxiabing
 * Date: 13-8-19 Time: 上午9:54
 * Email: wuxiabing@myhexin.com
 */

class lib_util_string
{
    static function splitStringByRow($string)
    {
        return self::splitStringByWord($string, "\r\n");
    }

    static function splitStringByPattern($string, $pattern)
    {
        if ($string && $pattern) {
            return preg_split($pattern, $string);
        } else {
            return false;
        }
    }

    static function splitStringByWord($string, $word)
    {
        if ($string && $word) {
            return explode($word, $string);
        } else {
            return false;
        }
    }

}