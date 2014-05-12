<?php
/**
 * 数组操作
 * @author wuxiabing
 *
 * 多维array
 * 合并、拆分、查找、替换、插入、排序、取值
 */

class lib_util_array
{

    /**多维数组多列排序
     * 这个地方坑爹的，多列的key有些还不能做为php的变量名
     * @param array $data
     * @param array $sort_data
     * @return array|boolean
     * @notice 此处不能使用call去调用，在php5.3中会报引用错误, 5.2/5.4中正常，其
    他版本未尝试
     * 现在使用很挫的eval来解决
     * call_user_func_array('array_multisort',$arguments);
     * $data = array(
     *  array(1,234,'t'=>41),
     *  array(1,232,'t'=>42),
     *  array(1,233,'t'=>43),
     *  array(1,233,'t'=>45),
     *  );
     * $sort_data = array(
     *  1=>'desc','t'=>'desc',
     * );
     *
     * notice: 中文排序时，如果是utf8，先转成gbk，再排序，再转回utf8
     */
    public static function sortMultiData($data, $sort_data)
    {
        if (!empty($data) && is_array($data) && !empty($sort_data) && is_array($sort_data)) {
            foreach ($sort_data as $k => $v) {
                if (!isset($data[0][$k])) {
                    unset($sort_data[$k]);
                }
            }
            if (!empty($sort_data)) {
                $num = 1;
                $temp_data = array();
                foreach ($sort_data as $k => $v) {
                    $temp_data[] = array(
                        'temp_sort_data' . $num => null,
                        'temp_sortby' => $v,
                        'temp_sort_key' => $k,
                        'temp_data_key' => 'temp_sort_data' . $num
                    );
                    $num++;
                }
                foreach ($data as $v) {
                    foreach ($temp_data as $k2 => $v2) {
                        $temp_data[$k2][$v2['temp_data_key']][] = $v[$v2['temp_sort_key']];
                    }
                }
                $arguments = array();
                foreach ($temp_data as $v) {
                    $arguments[] = $v[$v['temp_data_key']];
                    $arguments[] = (strtolower($v['temp_sortby']) == 'desc') ? SORT_DESC : SORT_ASC;
                }
                $arguments[] = & $data; //这个地方比较特殊，需要加上引用"&"
                if (count($sort_data) > 0 && !empty($arguments)) {
                    $exec_string = 'array_multisort(';
                    foreach ($arguments as $k => $v) {
                        $exec_string .= "\$arguments[$k],";
                    }

                    $exec_string = substr($exec_string, 0, strlen($exec_string) - 1);
                    $exec_string .= ');';
                    eval($exec_string);
                }
                //call_user_func_array('array_multisort',$arguments);
            }
        }
        return $data;
    }

    /*
     * sortMultiNum($data, 't', 'desc');
     * 按照数组中的数值大小排序，原先由array_multisort实现，现用自行算法实现，原先无法满足要求。
     * 时间复杂度： 2*o(n)
     *
     * $data = array(
     * array(1, 234, 't' => 41),
     * array(1, 232, 't' => 42),
     * array(1, 233, 't' => 43),
     * array(1, 233, 't' => 45),
     * );
     */
    public static function sortMultiNum($data, $key, $type = 'desc')
    {
        if (!empty($data) && is_array($data)) {
            $item = self::rand($data);
            if (!isset($item[$key])) {
                return $data;
            }
            $count = $category = array();
            foreach ($data as $v) {
                $category[$v[$key]][] = $v;
                if (isset($count[$v[$key]])) {
                    $count[$v[$key]]++;
                } else {
                    $count[$v[$key]] = 1;
                }
            }
            if ($type == 'desc') {
                arsort($count);
            } else {
                asort($count);
            }
            $data = array();
            foreach ($count as $k => $v) {
                foreach ($category[$k] as $v2) {
                    $data[] = $v2;
                }
            }
        }
        return $data;
    }

    /*
     * 桉数组中数值出现的次数排序
     *
     * $data = array(
     * array(1, 234, 't' => 41),
     * array(1, 232, 't' => 42),
     * array(1, 232, 't' => 42),
     * array(1, 233, 't' => 43),
     * array(1, 233, 't' => 45),
     * array(1, 233, 't' => 45),
     * array(1, 233, 't' => 45),
     * );
     * sortMultiCount($data, 't', 'desc');
     */
    public static function sortMultiCount($data, $key, $type = 'desc')
    {
        if (!empty($data) && is_array($data)) {
            $item = self::rand($data);
            if (!isset($item[$key])) {
                return $data;
            }
            $count_array = array();
            foreach ($data as $v) {
                if (isset($count_array[$v[$key]])) {
                    $count_array[$v[$key]]++;
                } else {
                    $count_array[$v[$key]] = 1;
                }
            }
            foreach ($data as $k => $v) {
                $col[$k] = $count_array[$v[$key]];
            }
            $type = ($type == 'desc') ? SORT_DESC : SORT_ASC;
            array_multisort($col, $type, $data);
        }
        return $data;
    }

    /**
     * 插入
     */
    public static function insert($data, $pos, $value)
    {
        if ($data) {
            if (is_array($pos)) {
                foreach ($pos as $k => $v) {
                    $val = isset($value[$k]) ? $value[$k] : null;
                    $data = self::insert($data, $v, $val);
                }
            } else {
                $pos = is_numeric($pos) ? $pos : -1;
                if ($pos >= count($data) || $pos < 0) {
                    $data[] = $value;
                } else if ($pos == 0) {
                    array_unshift($data, $value);
                } else {
                    $prevArr = array_splice($data, 0, $pos);
                    $prevArr[] = $value;
                    $data = array_merge($prevArr, $data);
                }
            }
        }
        return $data;
    }

    /**
     * 合并数组，进行深、浅遍历覆盖参数
     */
    public static function merge(array $arr1, array $arr2, $deep = true)
    {
        $result = array();
        if ($deep) { //深度遍历
            foreach ($arr1 as $k => $v) {
                if (isset($arr2[$k])) {
                    if (is_array($v)) {
                        $result[$k] = self::merge($v, $arr2[$k]);
                    } else {
                        $result[$k] = array_merge($v, $arr2[$k]);
                    }
                    unset($arr2[$k]);
                } else {
                    $result[$k] = $v;
                }
            }
        } else {
            foreach ($arr1 as $k => $v) {
                if (isset($arr2[$k])) {
                    $result[$k] = array_merge($v, $arr2[$k]);
                    unset($arr2[$k]);
                }
            }
        }
        foreach ($arr2 as $k => $v) {
            $result[$k] = $v;
        }
        return $result;
    }

    /**
     * 根据数值分割数组
     */
    public static function split(array $data, $split_num)
    {
        if (!empty($data) && ($split_num > 0) && is_array($data)) {
            $result = array();
            $split_num = (int)$split_num;
            $arr_num = ceil(count($data) / $split_num);
            while ($arr_num > 0) {
                $result[] = array_splice($data, 0, $split_num);
                $arr_num--;
            }
            return $result;
        }
        return $data;
    }

    public static function diff(array $arr1, array $arr2)
    {

    }

    /**
     * 随机取值
     * array_rand()随机取值
     * 有点懒，不喜欢rand出key，直接rand出value
     */
    public static function rand(array $data, $num = 1)
    {
        if (!empty($data) && $num >= 1) {
            $num = (int)$num;
            if ($num >= count($data)) {
                return $data;
            }
            if ($num == 1) {
                $key = array_rand($data);
                return $data[$key];
            } else {
                $result = array();
                $keys = array_rand($data, $num);
                foreach ($keys as $v) {
                    $result[] = $data[$v];
                }
                return $result;
            }
        }
        return null;
    }

    /**
     * 替换key操作
     * 分深、浅两种替换；
     * 深替换：递归替换所有key
     * 浅替换：替换最外一层key
     */
    public static function replaceKey(array $data, $ori_key, $replace_key, $save = true, $deep = true)
    {
        if (!empty($data) && isset($ori_key) && $replace_key) {
            if ($deep) {
                foreach ($data as $k => $v) {
                    if ($k === $ori_key) {
                        if ($save === false) {
                            unset($data[$k]);
                        }
                        if (is_array($v)) {
                            $data[$replace_key] = self::replaceKey($v, $ori_key, $replace_key, $save, $deep);
                        } else {
                            $data[$replace_key] = $v;
                        }
                    } else {
                        if (is_array($v)) {
                            $data[$k] = self::replaceKey($v, $ori_key, $replace_key, $save, $deep);
                        }
                    }
                }
            } else {
                if (isset($data[$ori_key])) {
                    $ori_temp_data = $data[$ori_key];
                    if ($save) {
                        $data[$replace_key] = $ori_temp_data;
                    } else {
                        unset($data[$ori_key]);
                        $data[$replace_key] = $ori_temp_data;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 值替换，分深、浅两种
     * 深替换，递归查询到对应的值进行替换
     * 浅替换，对最外层的值进行替换
     */
    public static function replaceValue(array $data, $ori_value, $replace_value, $deep = true)
    {
        if (!empty($data) && isset($ori_value) && isset($replace_value)) {
            if ($deep) {
                foreach ($data as $k => $v) {
                    if ($v === $ori_value) {
                        $data[$k] = $replace_value;
                    } else {
                        if (is_array($v)) {
                            $data[$k] = self::replaceValue($v, $ori_value, $replace_value, $deep);
                        }
                    }
                }
            } else {
                foreach ($data as $k => $v) {
                    if ($v === $ori_value) {
                        $data[$k] = $replace_value;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 删除数组中的key,不分深浅，直接深删除
     */
    public static function deleteKey($data, $key)
    {
        if (!empty($data) && is_array($data) && $key) {
            foreach ($data as $k => $v) {
                if ($k === $key) {
                    unset($data[$k]);
                } else {
                    if (is_array($v)) {
                        $data[$k] = self::deleteKey($v, $key);
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 计算某个key的数组
     */
    public static function sumItem($data, $key)
    {
        $result = 0;
        if (!empty($data) && is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $itemValue = self::sumItem($v, $key);
                    $result += $itemValue;
                } else {
                    if (($k === $key) && is_numeric($v)) {
                        $result = $result + $v;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 搜索数组中的item
     */
    public static function searchItem($data, $key, $value)
    {
        $result = array();
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $v) {
                if (isset($v[$key]) && ($v[$key] === $value)) {
                    $result = $v;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * 搜索数组中的item，并返回key/value
     */
    public static function searchNode($data, $key, $value)
    {
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $k => $v) {
                if (isset($v[$key]) && ($v[$key] === $value)) {
                    return array('key' => $k, 'value' => $v);
                }
            }
        }
        return false;
    }

    /**
     * 搜索数组中的item，返回计数
     */
    public static function searchNodeCount($data, $key, $value)
    {
        $count = 0;
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $v) {
                if (isset($v[$key]) && ($v[$key] === $value)) {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * 获取某个节点的值
     * 某个二维数组，获取第一列，第一行数组
     * lib_util_array::getNode($arr, 1, 1)
     */
    public static function getNode(array $data, $row, $col)
    {
        $result = null;
        if ($row >= 1 && $col >= 1 && !empty($data)) {
            $row = (is_int($row)) ? $row : (int)$row;
            $col = (is_int($col)) ? $col : (int)$col;
            $temp_result = array_slice($data, $row - 1, 1);
            $key = array_rand($temp_result);
            $temp_result = $temp_result[$key];
            $col_num = 1;
            foreach ($temp_result as $v) {
                if ($col_num == $col) {
                    $result = $v;
                    break;
                }
                $col_num++;
            }
        }
        return $result;
    }

}