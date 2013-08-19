<?php
/*
 * ant loader
 * ������ظ����ļ�
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
     * �������ɿ�������ģ�飬��ͼ�������ַ������,Ҳ�涨�˿�ܺ���Ŀ��Ŀ¼�ṹ
     * @name        load
     * @param    $__type �ַ���,ȡֵΪ act,tpl,cache,request,module
     * @param    $rs ��Դ
     * @param    $act ������
     * @access    public
     * @return     ����·����ʧ�ܷ���false
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
