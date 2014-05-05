<?php
/**
 * xml操作集合
 * @author wuxiabing
 *
 */

class lib_util_xml
{
    static function xmlToArray($xml)
    {
        return json_decode(json_encode($xml), true);
    }

    static function readXml($resource)
    {
        if (is_file($resource)) {
            $result = simplexml_load_file($resource);
        } else if (is_string($resource)) {
            $result = simplexml_load_string($resource);
        } else {
            $result = false;
        }
        return $result;
    }

    static function readXmlToArray($resource)
    {
        $result = self::readXml($resource);
        if ($result !== false) {
            $result = self::xmlToArray($result);
        }
        return $result;
    }
}