<?php
/**
 * 一些简单通用功能封装
 * @author wuxiabing
 */

class lib_util_common
{
    /**
     * 获取IP
     * @static
     * @return string
     */
    static function returnIp()
    {
        if ($_SERVER['REMOTE_ADDR'] && self::ipIsTrue($_SERVER['REMOTE_ADDR'])) {
            $ip = trim($_SERVER['REMOTE_ADDR']);
        } else {
            $ip = '-1';
        }
        return $ip;
    }

    /**
     * 判断是否是一个IPV4地址
     */
    static function ipIsTrue($ip)
    {
        return (preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $ip)) ? true : false;
    }

    /*
     * 转成gbk编码
     */
    static function toGBK($data = null, $from_charset = 'utf-8', $to_charset = 'gbk')
    {
        if (empty($data)) return null;

        if (is_array($data)) {
            $result = array();
            foreach ($data as $k => $v) {
                $result[$k] = self::toGBK($v, $from_charset, $to_charset);
            }
            $data = $result;
        } else {
            $data = iconv($from_charset, $to_charset, $data);
        }

        return $data;
    }

    /*
     * 转成utf8编码
     */
    static function toUTF8($data, $from_charset = 'gbk')
    {
        return self::toGBK($data, $from_charset, 'utf-8');
    }

    /**
     * 获取完整的url
     * @static
     * @param string $url
     * @param array $params
     * @param boolean $is_cover 是否覆盖url中的参数，默认覆盖
     * @return string
     */
    static function getUrl($url = '', $params = array(), $is_cover = true)
    {
        if ($url) {
            if (!empty($params) && is_array($params)) {
                $u = '';
                $p = array();
                $arr = parse_url($url);
                $u .= isset($arr['scheme']) ? $arr['scheme'] . '://' : '';
                $u .= isset($arr['host']) ? $arr['host'] : '';
                $u .= isset($arr['port']) ? ':' . $arr['port'] : '';
                $u .= isset($arr['path']) ? $arr['path'] : '';
                if (isset($arr['query'])) {
                    parse_str($arr['query'], $p);
                }
                if (!empty($p)) {
                    if ($is_cover) {
                        foreach ($p as $k => $v) {
                            $params[$k] = isset($params[$k]) ? $params[$k] : $v;
                        }
                    } else {
                        foreach ($p as $k => $v) {
                            $params[$k] = $v;
                        }
                    }
                }
                $url = $u . '?';
                foreach ($params as $k => $v) {
                    $url .= $k . '=' . urlencode($v) . '&';
                }
                $url = substr($url, 0, strlen($url) - 1);
            }
        }
        return $url;
    }

    /**
     * 获取默认值
     */
    static function formatString($str, $default = '')
    {
        return isset($str) ? $str : $default;
    }

    /**
     * SetGP
     */
    static function getGP($key, $default = '', $xss = 1)
    {
        if (isset($_POST[$key])) {
            $value = isset($_POST[$key]) ? $_POST[$key] : $default;
        } else {
            $value = isset($_GET[$key]) ? $_GET[$key] : $default;
        }

        if ($xss == 2) {
            $value = self::filterHttp($value);
        } else if ($xss == 1) {
            $value = self::filterXss($value);
        } else {
            $value = htmlspecialchars($value);
        }
        return $value;
    }

    /**
     * 过滤xss
     * @static
     * @param  $str
     * @return mixed
     */
    static function filterXss($str)
    {
        $str = str_replace(array('"', "'", '/'), '', $str);
        $str = htmlspecialchars($str);
        return preg_replace('/[\<\>\!\[\]\{\}\(\)\;\\\]/i', '', $str);
    }

    /**
     * 过滤http
     * @static
     * @param  $str
     * @return mixed
     */
    static function filterHttp($str)
    {
        $str = str_replace(array('"', "'"), '', $str);
        return preg_replace('/[\<\>\!\[\]\{\}\(\)\;\\\]/i', '', $str);
    }

    /**
     * 过滤GPC
     * @static
     * @param
     * @return mixed
     */
    static function filterGpc()
    {
        foreach ($_GET as $key => $value) {
            $_GET[$key] = self::filterXss($value);
        }
        foreach ($_POST as $key => $value) {
            $_POST[$key] = self::filterXss($value);
        }
        foreach ($_COOKIE as $key => $value) {
            $_COOKIE[$key] = self::filterXss($value);
        }
    }

}
