<?php
/**
 * 文件操作
 * @author wuxiabing
 *
 * 提供读写操作
 */

class lib_util_file
{

    /**
     * 读取文件操作
     */
    public static function read($file, $length = 4096, $extra = array())
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
                        $separate = isset($extra['separate']) ? $extra['separate'] : ',';
                        while (($buffer = fgetcsv($handle, $length, $separate)) !== false) {
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

    /**
     * 删除目录
     */
    public static function rmdir($dir, $removeSelf = false)
    {
        if ($handle = opendir($dir)) {
            while (false !== ($item = readdir($handle))) {
                if ($item != '.' && $item != '..') {
                    $path = $dir . DS . $item;
                    if (is_dir($path)) {
                        self::rmdir($path, true);
                    } else {
                        unlink($path);
                    }
                }
            }
            closedir($handle);
            if ($removeSelf) {
                rmdir($dir);
            }
        }
    }

    public static function write($file, $string = '')
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

    /**
     * 用于返回当前文件夹下的所有文件
     */
    public static function getFiles($dir)
    {
        $files = array();
        if (is_dir($dir)) {
            if ($dirHandle = opendir($dir)) {
                while ($file = readdir($dirHandle)) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    array_push($files, $file);
                }
                closedir($dirHandle);
            }
        }
        return $files;
    }

    /**
     * 检测目录下是否为空
     */
    public static function hasFile($dir)
    {
        $ret = false;
        if (is_dir($dir)) {
            if ($dirHandle = opendir($dir)) {
                while ($file = readdir($dirHandle)) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    $ret = true;
                    break;
                }
                closedir($dirHandle);
            }
        }
        return $ret;
    }
}
