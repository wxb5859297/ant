<?php
/*
 * ant loader
 * 自动加载器
 */
class antl
{
    private static $instance = null;
    public static $loadFiles = array();
    public $display_data;

    private function __construct()
    {
    }

    /**
     * @static
     * @return antl
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @param $type
     * @param $rs
     * @param string $act
     * @param null $data
     * @return bool|string  绝对路径，失败返回false
     *
     */
    public function load($type, $rs, $act = '', $data = null)
    {
        $file = $this->pathFix($type, $rs, $act, $data);
        if (file_exists($file)) {
            if ($type == 'tpl') {
                if ($data) extract($data);
                $this->display_data = $data;
                $display_param = $data;
            }
            if (!isset(self::$loadFiles[$file])) {
                self::$loadFiles[md5($file)] = $file;
                include $file;
            }
            return $file;
        }
        return false;
    }

    public function pathFix($type, $rs, $act = '', $data = null)
    {
        switch ($type) {
            case 'act':
                return PATH_RS . $rs . DS . $act . '.php';
            case 'tpl':
                if ($rs === null)
                    return PATH_TPL . $act . '.php';
                else
                    return PATH_TPL . $rs . DS . $act . '.php';
            case 'request':
                return PATH_REQUEST . $rs . DS . $act . '.php';
            case 'module':
                return PATH_MODULE . $rs . '.php';
            case 'file':
                return PATH_ROOT . $rs;
            default:
                return false;
        }
    }

    public function loadTpl($rs, $act, $data = null)
    {
        return $this->load('tpl', $rs, $act, $data);
    }

    public static function autoload($c)
    {
        $c = strtolower($c);
        $path = str_replace('_', '/', $c);
        $file = PATH_ROOT . $path . '.php';
        if (file_exists($file) && !isset(self::$loadFiles[md5($file)])) {
            self::$loadFiles[md5($file)] = $file;
            require $file;
        }
    }

    public static function useAutoload()
    {
        spl_autoload_register(array('antl', 'autoload'));
    }
}
