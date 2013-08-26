<?php
/**
 * Core of Ant
 */
$path = dirname(__FILE__);
require $path . '/antconst.php'; //ant框架常量
require $path . '/ante.php'; //ant框架堆栈器
require $path . '/antl.php'; //ant框架加载器
require $path . '/antc.php'; //ant框架控制器
require $path . '/antp.php'; //ant框架模板器
require $path . '/antr.php'; //ant框架过滤器
//require $path . '/antd.php'; //todo ant框架驱动控制器
//require $path . '/antcache.php'; //todo ant框架cache层

class ant
{
    /**
     * @var ant
     */
    private static $instance = null;
    /**
     * @var callback function
     */
    public static $auth_function = null;
    /**
     * @var Function
     */
    public static $msg_function = null;
    /**
     * @var ante
     */
    public static $error = null;
    /**
     * @var ant_info
     */
    private $ant_info = null;

    private function __construct()
    {
        ini_set('magic_quotes_gpc', 'Off');
        if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
        if (!defined('PATH_ROOT')) define('PATH_ROOT', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
        if (!defined('PATH_RS')) define('PATH_RS', PATH_ROOT . 'rs' . DS);
        if (!defined('PATH_CACHE')) define('PATH_CACHE', PATH_ROOT . 'cache' . DS);
        if (!defined('PATH_REQUEST')) define('PATH_REQUEST', PATH_ROOT . 'request' . DS);
        $view_name = (defined('VIEW_NAME')) ? VIEW_NAME : 'view';
        if (!defined('PATH_VIEW')) define('PATH_VIEW', PATH_ROOT . $view_name . DS);
        if (!defined('PATH_TPL')) define('PATH_TPL', PATH_VIEW . 'html' . DS);
        if (!defined('PATH_CONFIG')) define('PATH_CONFIG', PATH_ROOT . 'config' . DS);
        if (!defined('APP_CONFIG')) define('APP_CONFIG', PATH_CONFIG . 'application.ini');
        if (!defined('SYSTEM_CONFIG')) define('SYSTEM_CONFIG', PATH_CONFIG . 'system.ini');
        if (!defined('DOCUMENT_ROOT')) define('DOCUMENT_ROOT',$_SERVER['DOCUMENT_ROOT']);
        if (!defined('URL_ROOT')) define('URL_ROOT', '');
        define('PATH_ANT', dirname(__FILE__) . DS);
        define('ANT_ENTER', basename($_SERVER['SCRIPT_FILENAME']));
        if(DOCUMENT_ROOT == PATH_ROOT){
            define('PATH_PROJECT',str_replace(DS,'',str_replace(dirname(PATH_ROOT),'',PATH_ROOT))); 
        }else{
            define('PATH_PROJECT', str_replace(DS,'',str_replace(DOCUMENT_ROOT,'',PATH_ROOT)));
        }

        $this->ant_info = $GLOBALS['ant'];
    }

    /**
     * 从这里开始运行
     * @return mix
     */
    public function run()
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            $p = explode('/', $_SERVER['PATH_INFO']);
            array_shift($p);
            $rs = isset($p[0]) ? trim($p[0]) : '';
            $act = isset($p[1]) ? trim($p[1]) : '';
            for ($i = 2, $l = count($p); $i < $l; $i += 2) {
                $_GET[$p[$i]] = isset($p[$i + 1]) ? $p[$i + 1] : '';
            }
        } else {
            $act = isset($_GET['act']) ? trim($_GET['act']) : '';
            $rs = isset($_GET['rs']) ? trim($_GET['rs']) : '';
        }
        if (empty($act) && empty($rs)) {
            $act = isset($_POST['act']) ? trim($_POST['act']) : 'index';
            $rs = isset($_POST['rs']) ? trim($_POST['rs']) : 'index';
        }

        $act = empty($act) ? 'index' : $act;
        $rs = empty($rs) ? 'index' : $rs;
        return self::action($rs, $act, array(), $_SERVER['REQUEST_METHOD']);
    }

    /**
     * action
     * 控制器执行模块，可以独立运行
     * @name        action
     * @param       $rs 资源
     * @param       $act 控制器
     * @param       $displayParam 需要在模板中展示的参数
     * @param       $type 指定请求类型
     * @access      static
     */
    public static function action($rs, $act, $display_param = array(), $type = 'GET')
    {
        $rs   = strtolower($rs);
        $act  = strtolower($act);
        $type = strtolower($type);

        if ((self::$auth_function === null) || (self::$auth_function && call_user_func_array(self::$auth_function, array($rs, $act)))) {
            $c = 'rs_' . $rs . '_' . $act;
            if (antl::getInstance()->load('act', $rs, $act)) {
                if (class_exists($c)) {
                    /**
                     * @var antc $c
                     */
                    $c = new $c(false);
                    $c->init($rs,$act);
                    $c->display_param = $display_param;
                    $r = self::getRequest($c);
                    $ret = true;
                    if ($c->useCache($r) == false) {
                        ob_start();
                        $ret = $c->exec($r); //execute c
                        $result = ob_get_clean();
                        $c->display(); //execute v
                        if(!$c->hasView()){
                            echo $result;
                        }
                    }
                    return $ret;
                }
            }

            //仅视图页面使用更加轻量的对象.
            $c = new antp($rs, $act);
            $c->loadData($display_param);
            if (!$c->display())
                self::E('CONTROLLER_NOT_FOUND');
            return false;
        }
    }

    /**
     * getRequest
     * 将过滤器载入，过滤器中"仅"保存过滤后的内容
     * @name    getRequest
     * @param   antc $o
     * @access  static
     * @return  antr     request请求对象,失败返回false
     */
    public static function getRequest($o)
    {
        /**
         * @var antr $r
         */
        $rs = $o->request_act[0];
        $act = $o->request_act[1];
        if (antl::getInstance()->load('request', $rs, $act)) {
            $c = "request_{$rs}_$act";
            if (class_exists($c)) {
                $r = new $c();
                $r->find_all_errors = false;
            } else
                $r = new antr();
        } else {
            $r = new antr();
        }
        $o->request = $r;
        $r->act = $o;
        $r->run();
        return $r;
    }

    /**
     * @return ant
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function E($err = "", $params = array())
    {
        if (defined('DEBUG')) {
            $ant = self::getInstance();
            $ant_info = $ant->ant_info;
            $title = $ant_info['core_error'][$err]['title'];
            $detail = $ant_info['core_error'][$err]['detail'];
            extract($params);
            $path_view = PATH_VIEW;
            $path_rs = PATH_RS;
            //$detail = eval('return "' . $detail . '";');
            eval("\$detail = \".$detail.\";");
            antp::info("wrong", "Ant core error!", $title, $detail);
        }
    }

    public function registerAuthFunction($func)
    {
        self::$auth_function = $func;
    }

    public function registerMsgFunction($func = null)
    {
        self::$msg_function = $func;
    }

    /**
     * 自动调用注册过的消息函数，并阻止外层控制器展示视图
     * @static
     * @param  $msg
     * @param int $status
     * @return
     */
    public static function message($msg, $status = 200)
    {
        $a = debug_backtrace();

        if (self::$msg_function == null) {
            return;
        }

        if (isset($a[1]['object']) && $a[1]['object'] instanceof antc) {
            $a[1]['object']->noView();
        }
        call_user_func_array(self::$msg_function, array($msg, $status));
    }

    /**
     * DEBUG
     * @static
     * @param  $error
     * @param null $errno
     * @param null $key
     * @return void
     */
    public static function setError($error, $errno = null, $key = null)
    {
        if (self::$error == null) self::$error = new ante();
        self::$error->setError($error, $errno, $key);
    }

    public static function getError($key = null)
    {
        if (self::$error == null) self::$error = new ante();
        return self::$error->getError($key);
    }

    public static function getErrorInfo($key = null)
    {
        if (self::$error == null) self::$error = new ante();
        return self::$error->getErrorInfo($key);
    }

    public static function printErrorStack()
    {
        self::$error->printErrorStack();
    }
    
    public function getAppConfig()
    {
        $config = parse_ini_file(APP_CONFIG,true);        
        return $config;
    }

    public function getDbConfig(){
        $rs = $this->getAppConfig();
        return $rs['db_info'];
    }

    public function getCacheConfig(){
        $rs = $this->getAppConfig();
        return $rs['cache_info'];
    }
}
