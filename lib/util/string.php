<?php
/**
 * 字符串操作
 * @author: wuxiabing
 */

class lib_util_string
{
    public static function splitByRow($string, $p = "\r\n")
    {
        return self::splitByWord($string, $p);
    }

    public static function splitByPattern($string, $pattern)
    {
        if ($string && $pattern) {
            return preg_split($pattern, $string);
        } else {
            return false;
        }
    }

    public static function splitByWord($string, $word)
    {
        if ($string && $word) {
            return explode($word, $string);
        } else {
            return false;
        }
    }

    /**
     * 截取字符串操作
     */
    public static function sub($str = '', $len = 32, $charset = 'utf-8', $endStr = '…')
    {
        $extra = '';
        if (iconv_strlen($str, $charset) > $len) {
            $extra = $endStr;
        }
        return iconv_substr($str, 0, $len, $charset) . $extra;
    }
}