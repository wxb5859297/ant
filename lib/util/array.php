<?php
/**
 * array�������
 * @author wuxiabing
 * @date 13-8-5 ����9:17
 */

class lib_util_array
{

    /**��ά�����������
     * ���е�key��Щ��������Ϊphp�ı�����,������Ҫtemp*αװЩ����
     * @param array $data
     * @param array $sort_data
     * @return array
     * @notice �˴�����ʹ��callȥ���ã���php5.3�лᱨ���ô���, 5.2/5.4�������������汾δ����
     * ����ʹ�úܴ��eval�����
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
                $arguments[] = & $data; //����ط��Ƚ����⣬��Ҫ��������"&"
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
     * ��ά�������򣬰���������
     * ����array_multisortʵ��,ʱ�临�Ӷ� 2*O(n)
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
     * ��ά�������򣬰���������
     * �˷�������ĳЩ����޷�ʹ�ã��������������ͬ���ܵķ���������array_multisortʵ��
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
                $col[$k] = $count_array[$v[$key]];//��ֵ���ֵĴ�������
            }
            $type = ($type == 'desc') ? SORT_DESC : SORT_ASC;
            array_multisort($col, $type, $data);
        }
        return $data;
    } 
    /**
     * todo ��Ҫд�ĸ���׳Щ
     * ��������Ԫ��
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

    /**�ϲ����飬�����ǳ�������ǲ���
     * @param array $arr1
     * @param array $arr2
     * @param bool $deep
     * @return array
     */
    static function merge(array $arr1, array $arr2, $deep = true)
    {
        $result = array();
        if ($deep) { //��ȱ���
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

    /**������ֵ�ָ�����
     * ��������һά�ģ��и���ɶ�ά   
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
     * ������飬�������������ȡ�����Ԫ��
     * array_rand()����ֻ��ȡ������
     * �������ˣ�����ֱ�����������ֵ
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
     * ���������еļ���
     * ���ǳ�����滻
     * �ֱ��������������������滻
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
     * �滻��ֵ�����ǳ�����滻
     */
    static function replaceValue(array $data, $ori_value, $replace_value, $deep = true){

    }

    /**
     * ��ȡ������ĳһ���ڵ�ļ�ֵ
     * �����ڶ�ά�����ȡ�ڵ�ֵ
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
