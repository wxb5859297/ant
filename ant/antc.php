<?php
/**
 * antc             资源基类，控制模块和视图部分
 * @name            antc
 */
class antc
{
    /**
     * 自执行返回值
     */
    public $self_execute_result = false;
    /**
     * 是否拥有视图，一些AJAX请求或者接口，往往没有视图
     * @var bool
     */
    protected $has_view = true;
    /**
     * 保存需要展示的数据
     * @var array
     */
    public $display_param = array();
    /**
     * 模板的 rs 用于指定模板位置，不同的控制器可以使用相同的模板
     * @var null
     */
    public $tprs = null;
    public $tpact = null;
    /**
     * ָ制定request的位置
     * @var array|null
     */
    public $request_act = null;
    /**
     * @var antp
     */
    public $tp;
    /**
     * @var antr
     */
    public $request;
    /**
     * 每个缓存文件个数
     * @var int
     */
    public $cache_page = 50;
    /**
     * 缓存时间长短，0：不缓存
     * @var int
     */
    public $cache = 0;
    /**
     * 缓存文件名
     * @var null
     */
    public $cache_filename = '';
    /**
     * 是否允许写缓存
     * @var bool
     */
    public $writeable = true;

    public $rs;
    public $act;

    /**
     * 由于静态函数run的实现过于复杂，对现有代码改动很大，所以最终采用冗余生成对
    象的方式来实现这个功能
     * 通过:
     * new rs_index_help(true);来自执行rs=index&act=help这个控制器对象
     * @param bool $self_execute
     * @param array $display_param
     */
    public function __construct($self_execute = false, $display_param = array())
    {
        if ($self_execute) {
            $name = get_class($this);
            $name_piece = explode('_', $name);
            $this->init(strtolower($name_piece[1]), strtolower($name_piece[2]));

            $r = ant::getRequest($this);
            $this->display_param = $display_param;
            $this->self_execute_result = $this->exec($r);
            $this->display();
        }
    }

    public function init($rs, $act)
    {
        $this->rs = $rs;
        $this->act = $act;
        if ($this->request_act === null)
            $this->request_act = array($this->rs, $this->act);
    }

    public function noView()
    {
        $this->has_view = false;
    }

    public function useView()
    {
        $this->has_view = true;
    }

    public function hasView()
    {
        return $this->has_view;
    }

    public function exec(antr $r)
    {

    }

    public function assign($name, $value)
    {
        $this->display_param[$name] = $value;
    }

    public function getAssign($name)
    {
        if (isset($this->display_param[$name]))
            return $this->display_param[$name];
        else
            return null;
    }

    public function display()
    {
        $this->tp = new antp($this->rs, $this->act);

        if ($this->has_view == false) return $this->tp;
        if ($this->tpact) $this->tp->act = $this->tpact;
        if ($this->tprs) $this->tp->rs = $this->tprs;
        $this->display_param['r'] = $this->request;
        $this->tp->loadData($this->display_param);

        if ($this->cache > 0 && $this->writeable) {
            $s = $this->tp->sdisplay();
            $fp = fopen($this->cache_filename, 'w');
            fwrite($fp, $s);
            fclose($fp);
            echo $s;
        } else {
            $this->tp->display();
        }

        return $this->tp;
    }

    public function useCache(antr $r)
    {
        $class_name = get_class($this);
        if ($this->cache <= 0) return false;

        $cache_id = urlencode(urldecode($r->getValue('cache_id'))); //避免重复urlencode

        $page_id = intval($r->getValue('page_id'));

        $hd_is_cache = intval($r->getValue('hd_is_cache'));
        if ($class_name == 'rs_index_blocklucklist' && $hd_is_cache != 1) return false;

        if ($cache_id) {
            $dir = $page_id ? (PATH_CACHE . $class_name . DS . $cache_id . DS) : (PATH_CACHE . $class_name . DS);
            $file_url = $page_id ? $page_id : $cache_id;
        } else {
            $dir = $page_id ? (PATH_CACHE . $class_name . DS) : PATH_CACHE;
            $file_url = $page_id ? $page_id : $class_name;
        }

        $fn = $dir . $file_url . '.html';

        $this->cache_filename = $fn;

        if (file_exists($dir) == false) {
            if (!mkdir($dir, 0755, true)) {
                $this->cache = 0;
            }
        }
        if ($_GET['show_cache_path'] == 1) {
            echo $fn;
        }

        if (file_exists($fn)) {
            $fp = fopen($fn, 'r');
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                if ($r->get('ant_clear_cache')->value() == 1) {
                } else {
                    $time = time() - filemtime($fn);
                    if ($class_name == 'rs_index_blocklucklist') {
                        //ajax请求，需要转码
                        echo iconv('gbk', 'utf-8', file_get_contents($fn));
                        return true;
                    }
                    if (($time / 60) <= $this->cache) {
                        echo file_get_contents($fn);
                        return true;
                    }
                }
                fclose($fp);
                @unlink($fn);
                $this->writeable = true; //授权写
            } else {
                $this->writeable = false; //不授权写
                echo file_get_contents($fn);
                return true;
            }
        }

        return false;
    }

    public function forceTp($act, $rs = null)
    {
        $this->tpact = $act;
        if ($rs) $this->tprs = $rs;
    }

    /**
     * 调用此函数，确保JSON对象格式固定
     * @param  $success
     * @param string $data
     * @param null $message
     * @return void
     */
    public function jsonResult($success, $data = '', $message = null)
    {
        $this->noView();
        $message = iconv('gbk', 'utf-8', $message);
        $c = new stdClass();
        /** @noinspection PhpUndefinedFieldInspection */
        $c->success = $success;
        $c->data = $data;
        if ($message !== null) {
            /** @noinspection PhpUndefinedFieldInspection */
            $c->message = $message;
        }
        $s = json_encode($c);
        if (isset($_GET['callback'])) {
            $s = addslashes($s);
            echo $_GET['callback'] . "(\"$s\")";
        } else
            echo $s;
    }

    public function jsonError()
    {
        $e = ant::getErrorInfo();
        if ($e) {
            $this->jsonResult(false, $e['errno'], $e['error']);
        } else {
            $this->jsonResult(true, '', 'no error');
        }
    }

    /**
     * 获取IP
     * @static
     * @return string
     */
    public static function returnIp()
    {
        $ip = "-1";
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_a = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            for ($i = 0; $i < count($ip_a); $i++) { //
                $tmp = trim($ip_a[$i]);
                if ($tmp == 'unknown' || $tmp == '127.0.0.1' || strncmp($tmp, '10.', 3) == 0 || strncmp($tmp, '172', 3) == 0 || strncmp($tmp, '192', 3) == 0)
                    continue;
                $ip = $tmp;
                break;
            }
        } else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = trim($_SERVER['HTTP_CLIENT_IP']);
        } else if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = trim($_SERVER['REMOTE_ADDR']);
        }

        return $ip;
    }

    /**
     * 返回格式化后的标准url
     * 很多时候我们会在url中不写index，但是这里会补上
     * @static
     * @return string
     */
    public static function returnUrl()
    {
        $uri = '';
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $rs = isset($_GET['rs']) ? $_GET['rs'] : 'index';
            $act = isset($_GET['act']) ? $_GET['act'] : 'index';

            ksort($_GET);
            $uri .= '/index.php?rs=' . $rs . '&act=' . $act;
            if (!empty($_GET)) {
                foreach ($_GET as $k => $v) {
                    if (in_array($k, array('rs', 'act'))) continue;
                    $uri .= "&$k=$v";
                }
            }
        }
        return $uri;
    }

    /**
     * ant支持这样调用一个控制器
     * ant::action('index','help');//调用帮助页面,但是这个页面的控制器在哪里无法定位，尤其
    是(IDE,项目组的新人)
     * 这里提供一种更加容易阅读的代码书写方式
     * rs_index_help::run();//代码在rs/index/help.php
     * 两种方法各有好处，但是在控制器命名不会改变的情况下，第二种将更加友好
     * 但是你必须复制这个代码到每个控制器中，否则__CLASS__无法正常使用，希望之后PHP能够提>供更好的支持
     *
     * @static
     * @param array $display_param
     * @param string $type
     * @return bool
     */
    public static function run($display_param = array(), $type = 'GET')
    {
        $name = __CLASS__;
        $name_piece = explode('_', $name);
        return ant::action($name_piece[1], $name_piece[2], $display_param, $type);
    }
}
