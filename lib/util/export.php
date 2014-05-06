<?php
/**
 * 导出类集合，下载xls、cvs等等
 * @author: wuxiabing
 * @email: wxb5859297@gmail.com
 *
 * 注意事项：
 * 1. xls、csv需要有header数据
 * 2. xls数据中若是0开头，想完全展示数据，需要在0前面加“‘”来保证数据完整
 *
 * demo:
 * lib_util_export::getInstance()->init($arr, 'xml')->export();
 * lib_util_export::getInstance()->init($arr, 'xls')->export();
 *
 * todo:
 * 1. xls的更多css样式支持;
 * 2. csv/xls格式支持多维数据，涉及到如何排列问题
 *
 * excel小问题杂烩：http://blog.sina.com.cn/s/blog_7f71e5260101d4gj.html
 */

class lib_util_export
{
    private $render_type;
    private $data = null;
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init($data, $render_type = 'json')
    {
        $this->data = $data;
        $this->render_type = empty($render_type) ? 'json' : $render_type;
        if (!in_array($this->render_type, array('json', 'xml', 'xls', 'csv'))) {
            $this->render_type = 'json';
        }
        return $this;
    }

    public function export()
    {
        if (!empty($this->data)) {
            $func = 'to' . ucfirst(strtolower($this->render_type));
            call_user_func_array(array(self::$instance, $func), func_get_args());
        }
    }

    private function toXls()
    {
        $fileName = isset($this->data['fileName']) ? $this->data['fileName'] : date('Y-m-d');
        $title = isset($this->data['title']) ? $this->data['title'] : '';
        $header = $this->data['header'];
        $data = $this->data['data'];
        $str = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
            <html xmlns='http://www.w3.org/1999/xhtml'>
            <head>
            <meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
            <title>$title</title>
            <style>
            td{
                text-align:center;
                font-size:12px;
                font-family:Arial, Helvetica, sans-serif;
                border:#1C7A80 1px solid;
                color:#152122;
    }
    table,tr{
        border-style:none;
    }
    .title{
        background:#7DDCF0;
        color:#FFFFFF;
        font-weight:bold;
        text-align:center;
    }
    </style>
        </head>

        <body>
        <table border='1'>";
        if ($header) {
            foreach ($header as $v) {
                if (is_array($v)) {
                    $moreHeader = true;
                    break;
                } else {
                    $moreHeader = false;
                }
            }
            $str .= "<tr>";
            foreach ($header as $k => $v) {
                if (is_array($v)) {
                    $keys = array_keys($v);
                    foreach ($keys as $title) {
                        $tempHeader[] = $v[$title];
                        $str .= '<td colspan="' . count($v[$title]) . '" class="title">' . $title . '</td>';
                    }
                } else {
                    if ($moreHeader) {
                        $str .= '<td rowspan="2" class="title">' . $v . '</td>';
                    } else {
                        $str .= '<td class="title">' . $v . '</td>';
                    }
                }
            }
            $str .= "</tr>";
            if ($tempHeader) {
                $str .= "<tr>";
                foreach ($tempHeader as $v) {
                    foreach ($v as $v2) {
                        $str .= '<td class="title">' . $v2 . '</td>';
                    }
                }
                $str .= "</tr>";
            }
        }
        if ($data) {
            foreach ($data as $v) {
                $str .= "<tr>";
                foreach ($v as $v2) {
                    if (is_array($v2)) {
                        foreach ($v2 as $v3) {
                            if (is_numeric($v3)) {
                                $str .= '<td style="mso-number-format:\'0.00\';text-align:right;">' . number_format($v3, 2, '.', '') . '</td>'; //默认数据右对齐
                            } else {
                                $str .= '<td>' . $v3 . '</td>';
                            }
                        }
                    } else {
                        if (is_numeric($v2)) {
                            $str .= '<td style="mso-number-format:\'0.00\';text-align:right;">' . number_format($v2, 2, '.', '') . '</td>'; //默认数据右对齐
                        } else {
                            $str .= '<td>' . $v2 . '</td>';
                        }
                    }
                }
                $str .= "</tr>";
            }
        }
        $str .= "</table>
        </body>
        </html>";
        header("Content-type:application/vnd.ms-excel");
        header("Cache-Control:no-cache");
        header("Content-Disposition:attachment;filename=" . $fileName . '.xls');
        echo $str;
    }

    private function toCsv()
    {
        $str = '';
        $fileName = isset($this->data['fileName']) ? $this->data['fileName'] : date('Y-m-d');
        $header = $this->data['header'];
        $data = $this->data['data'];
        if ($header) {
            array_unshift($data, $header);
        }
        if ($data) {
            foreach ($data as $v) {
                foreach ($v as $v2) {
                    $str .= $v2 . "\t";
                }
                $str .= "\n";
            }
        }
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $fileName . '.xls');
        echo $str;
    }

    private function toJson()
    {
        echo json_encode($this->data);
    }

    private function toXml()
    {
        header('Content-Type: text/xml');
        $str = '<?xml version="1.0" encoding="utf-8"?>';
        $str .= '<root>';
        $str .= $this->getXmlItem($this->data);
        $str .= '</root>';
        echo $str;
    }

    private function getXmlItem($arr)
    {
        $xml = '';
        if (!empty($arr) && is_array($arr)) {
            foreach ($arr as $k => $v) {
                $nodeName = is_numeric($k) ? 'item_' . $k : $k;
                $xml .= "<$nodeName>";
                if (is_array($v)) {
                    $xml .= $this->getXmlItem($v);
                } else {
                    $xml .= htmlspecialchars($v);
                }
                $xml .= "</$nodeName>";
            }
        }
        return $xml;
    }
}