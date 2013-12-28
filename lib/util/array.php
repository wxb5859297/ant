<?php
/**
 * array数组操作
 * @author wuxiabing
 * @date 13-8-5 晚上9:17
 */

class lib_util_array
{

    /**多维数组多列排序
     * 多列的key有些还不能做为php的变量名,所以需要temp*伪装些变量
     * @param array $data
     * @param array $sort_data
     * @return array
     * @notice 此处不能使用call去调用，在php5.3中会报引用错误, 5.2/5.4中正常，其他版本未尝试
     * 现在使用很挫的eval来解决
     * call_user_func_array('array_multisort',$arguments);
     */
    static function sortMultiData($data, $sort_data)
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
     * 多维数组排序，按给个键名
     * 不用array_multisort实现,时间复杂度 2*O(n)
     * @param array $data
     * @param array $sort_data
     * @return array
     */
    static function sortMultiNum($data,$key,$type = 'desc')
    {
        if(!empty($data) && is_array($data)){
            $item = self::rand($data);
            if(!isset($item[$key])){
                return $data;
            }
            $count = $category = array();
            foreach($data as $k=>$v){
                $category[$v[$key]][] = $v;
                if(isset($count[$v[$key]])){
                    $count[$v[$key]]++;
                }else{
                    $count[$v[$key]] = 1;
                } 
            }
            if($type == 'desc'){
                arsort($count);
            }else{
                asort($count);
            }
            $data = array();
            foreach($count as $k=>$v){
                foreach($category[$k] as $v2){
                    $data[] = $v2;
                }
            }
        }
        return $data;
    } 

    /*
     * 多维数组排序，按给个键名
     * 此方法，在某些情况无法使用，因此另外增加相同功能的方法，不用array_multisort实现
     * @param array $data
     * @param array $sort_data
     * @return array
     */
    static function sortMultiCount($data,$key,$type = 'desc')
    {
        if(!empty($data) && is_array($data)){
            $item = self::rand($data);
            if(!isset($item[$key])){
                return $data;
            }
            $count_array = array();
            foreach ($data as $k => $v) {
                if(isset($count_array[$v[$key]])){
                    $count_array[$v[$key]]++;
                }else{
                    $count_array[$v[$key]] = 1;
                }
            }
            foreach($data as $k => $v) {
                $col[$k] = $count_array[$v[$key]];//按值出现的次数排序。
            }
            $type = ($type == 'desc') ? SORT_DESC : SORT_ASC;
            array_multisort($col, $type, $data);
        }
        return $data;
    } 
    /**
     * todo 需要写的更健壮些
     * 插入数组元素
     */
    static function insert($data, $pos, $value)
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

    /**合并数组，进行深、浅遍历覆盖参数
     * @param array $arr1
     * @param array $arr2
     * @param bool $deep
     * @return array
     */
    static function merge(array $arr1, array $arr2, $deep = true)
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

    /**根据数值分割数组
     * 该数组是一维的，切割后变成二维   
     * @param $data
     * @param $split_num
     * @return array
     */
    static function split(array $data, $split_num)
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

    static function diff(array $arr1, array $arr2)
    {

    }

    /**
     * 随机数组，根据随机数量，取出随机元素
     * array_rand()函数只能取出键名
     * 我是懒人，所以直接用随机出键值
     */
    static function rand(array $data,$num = 1){
        if(!empty($data) && $num >=1){
            $num = (int)$num;
            if($num >= count($data)){
                return $data;
            }
            if($num == 1){
                $key = array_rand($data);
                return $data[$key];
            }else{
                $result = array();
                $keys = array_rand($data,$num);
                foreach($keys as $v){
                    $result[] = $data[$v];
                }
                return $result;
            }
        }
        return null;
    }

    /**
     * 更换数组中的键名
     * 分深、浅两种替换
     * 分保留、不保留键名两种替换
     */
    static function replaceKey(array $data, $ori_key, $replace_key, $save = true, $deep = true){
        if(!empty($data) && $ori_key && $replace_key){
            if($deep){
                foreach($data as $k=>$v){
                    if($k == $ori_key){
                        if($save){
                            $data[$replace_key] = $v;
                        }else{
                            unset($data[$k]);
                            $data[$replace_key] = $v;
                        }
                    }else{
                        if(is_array($v)){
                            $data[$k] = self::replaceKey($v,$ori_key,$replace_key,$save,$deep);
                        }
                    }
                } 
            }else{
                if(isset($data[$ori_key])){
                    $ori_temp_data = $data[$ori_key];
                    if($save){
                        $data[$replace_key] = $ori_temp_data; 
                    }else{
                        unset($data[$ori_key]);
                        $data[$replace_key] = $ori_temp_data; 
                    }
                }
            }
        }
        return $data;
    }

    /**
     * 替换键值，分深、浅两种替换
     */
    static function replaceValue(array $data, $ori_value, $replace_value, $deep = true){

    }

    /**
     * 获取数组中某一个节点的键值
     * 多用于二维数组获取节点值
     */
    static function getNode(array $data, $row, $col)
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
