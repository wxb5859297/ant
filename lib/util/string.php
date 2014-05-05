<?php
/**
 * 字符串操作
 * @author: wuxiabing
 */

class lib_util_string
{
    static function splitByRow($string)
    {
        return self::splitByWord($string, "\r\n");
    }

    static function splitByPattern($string, $pattern)
    {
        if ($string && $pattern) {
            return preg_split($pattern, $string);
        } else {
            return false;
        }
    }

    static function splitByWord($string, $word)
    {
        if ($string && $word) {
            return explode($word, $string);
        } else {
            return false;
        }
    }

}