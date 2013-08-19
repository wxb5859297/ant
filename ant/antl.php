<?php
/*
 * ant loader
 * 负责加载各种文件
 */
class antl
{
    private static $instance = null;
    public static $loadFiles = array();
    public $display_data;

    private function __construct(){
    }
    /**
     * @static
     * @return antl
     */
    public static function getInstance()
    {
        if (self::$instance == null){
            self::$instance = new self();
        } 
        return self::$instance;
    }

    /**
     * load
     * 用于生成控制器，模块，视图的载入地址并载入,也规定了框架和项目的目录结构
     * @name        load
     * @param    $__type 字符串,取值为 act,tpl,cache,request,module
     * @param    $rs 资源
     * @param    $act 控制器
     * @access    public
     * @return     绝对路径，失败返回false
     * antl::getInstance()->load('file','file/gameconfig/212_interface.php');
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
            if(!isset(self::$loadFiles[$file])){
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
        case 'file':
            return PATH_ROOT.$rs;
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
        $path = str_replace('_','/',$c);
        $file = PATH_ROOT.$path.'.php';
        if(file_exists($file) && !isset(self::$loadFiles[md5($file)])){
            self::$loadFiles[md5($file)] = $file;
            require $file;
        }
    }

    public static function useAutoload()
    {
        spl_autoload_register(array('antl','autoload'));
    }
}
