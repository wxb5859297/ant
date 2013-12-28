<?php
/**文件操作
 * @author wuxiabing
 * @date 12-5-11 晚上10:30
 */

class lib_util_file
{

    /**读取文件操作
     * @param $file
     * @param int $length
     * @param array $extra
     * @return array|bool
     */
    static function read($file, $length = 4096, $extra = array())
    {
        if (file_exists($file)) {
            $base_file = basename($file);
            $arr = explode('.', $base_file);
            $file_type = array_pop($arr);
            $result = array();
            if ($handle = fopen($file, 'r')) {
                switch ($file_type) {
                    case 'txt':
                        while (($buffer = fgets($handle, $length)) !== false) {
                            $result[] = trim($buffer);
                        }
                        break;
                    case 'csv':
                        $separat = isset($extra['separat']) ? $extra['separat'] : ',';
                        while (($buffer = fgetcsv($handle, $length, $separat)) !== false) {
                            $result[] = $buffer;
                        }
                        break;
                }
                fclose($handle);
            }
            return $result;
        }
        return false;
    }

    static function rm()
    {

    }

    /**
     * 分割字符串
     */
    static function splitString()
    {
    }

    static function write($file, $string = '')
    {
        if ($handle = fopen($file, 'a')) {
            if (is_writable($file) && file_exists($file)) {
                if (fwrite($handle, $string)) {
                    return true;
                }
            }
            fclose($handle);
        }
        return false;
    }
}
