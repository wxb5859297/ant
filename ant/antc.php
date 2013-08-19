<?php
/**
 * antc             ��Դ���࣬����ģ�����ͼ����
 * @name            antc
 */
class antc
{
    /**
     * ��ִ�з���ֵ
     */
    public $self_execute_result = false;
    /**
     * �Ƿ�ӵ����ͼ��һЩAJAX������߽ӿڣ�����û����ͼ
     * @var bool
     */
    protected $has_view = true;
    /**
     * ������Ҫչʾ������
     * @var array
     */
    public $display_param = array();
    /**
     * ģ��� rs ����ָ��ģ��λ�ã���ͬ�Ŀ���������ʹ����ͬ��ģ��
     * @var null
     */
    public $tprs = null;
    public $tpact = null;
    /**
     * ָ��request��λ��
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
     * ÿ������Ŀ¼�е��ļ�����
     * @var int
     */
    public $cache_page = 50;
    /**
     * ����ʱ�䳤��
     * @var int
     */
    public $cache = 0;
    /**
     * �����ļ���
     * @var null
     */
    public $cache_filename = '';
    /**
     * �Ƿ�����д����
     * @var bool
     */
    public $writeable = true;

    public $rs;
    public $act;

    /**
     * ���ھ�̬����run��ʵ�ֹ��ڸ��ӣ������д���Ķ��ܴ��������ղ����������ɶ���ķ�ʽ��ʵ���������
     * ͨ��:
     * new rs_index_help(true);����ִ��rs=index&act=help�������������
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

        $cache_id = urlencode(urldecode($r->getValue('cache_id'))); //�����ظ�urlencode

        $page_id = intval($r->getValue('page_id'));

        $hd_is_cache = intval($r->getValue('hd_is_cache'));
        if($class_name == 'rs_index_blocklucklist' && $hd_is_cache != 1) return false;

        if ($cache_id) {
            $dir = $page_id ? (PATH_CACHE . $class_name . DS .$cache_id  . DS) : (PATH_CACHE . $class_name . DS);
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
        //��ʾ�����ַ && $_SERVER['HTTP_HOST'] == 'test.dou.pps.tv'
        if ($_GET['show_cache_path']==1) {
            echo $fn;
        }

        if (file_exists($fn)) {
            $fp = fopen($fn, 'r');
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                if ($r->get('ant_clear_cache')->value() == 1 && '180.168.84.109' == $this->returnIp()) {
                    //��̨�����建��ķ���
                } else {
                    $time = time() - filemtime($fn);
                    if($class_name == 'rs_index_blocklucklist'){
                        //ajax������Ҫת��
                        echo iconv('gbk','utf-8',file_get_contents($fn));
                        return true;
                    }
                    if (($time / 60) <= $this->cache){
                        echo file_get_contents($fn);
                        return true;
                    }
                }
                fclose($fp);
                @unlink($fn);
                $this->writeable = true; //��Ȩд
            } else {
                $this->writeable = false; //����Ȩд
                if($class_name == 'rs_index_blocklucklist'){
                    //ajax������Ҫת��
                    echo iconv('gbk', 'utf-8', file_get_contents($fn));
                } else {
                    echo file_get_contents($fn);
                }//ֻ�ܶ�
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
     * ���ô˺�����ȷ��JSON�����ʽ�̶�
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
     * ��ȡIP
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
     * ���ظ�ʽ����ı�׼url
     * �ܶ�ʱ�����ǻ���url�в�дindex����������Ჹ��
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
     * ant֧����������һ��������
     * ant::action('index','help');//���ð���ҳ��,�������ҳ��Ŀ������������޷���λ��������(IDE,��Ŀ�������)
     * �����ṩһ�ָ��������Ķ��Ĵ�����д��ʽ
     * rs_index_help::run();//������rs/index/help.php
     * ���ַ������кô��������ڿ�������������ı������£��ڶ��ֽ������Ѻ�
     * ��������븴��������뵽ÿ���������У�����__CLASS__�޷�����ʹ�ã�ϣ��֮��PHP�ܹ��ṩ���õ�֧��
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
