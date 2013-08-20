<?php
/**
 * array�������
 * @author wuxiabing
 * @date 13-8-5 ����9:17
 */

class lib_util_array
{

    /**��ά�����������
     * ����ط��ӵ��ģ����е�key��Щ��������Ϊphp�ı�����
     * @param array $data
     * @param array $sortData
     * @return array|boolean
     * @notice �˴�����ʹ��callȥ���ã���php5.3�лᱨ���ô���, 5.2/5.4�������������汾δ����
     * ����ʹ�úܴ��eval�����
     * call_user_func_array('array_multisort',$arguments);
     */
    static function sortMultiData($data, $sortData)
    {
        if (!empty($data) && is_array($data) && !empty($sortData) && is_array($sortData)) {
            foreach ($sortData as $k => $v) {
                if (!isset($data[0][$k])) {
                    unset($sortData[$k]);
                }
            }
            if (!empty($sortData)) {
                $num = 1;
                $tempData = array();
                foreach ($sortData as $k => $v) {
                    $tempData[] = array(
                        'tempSortData' . $num => null,
                        'tempSortBy' => $v,
                        'tempSortKey' => $k,
                        'tempDataKey' => 'tempSortData' . $num
                    );
                    $num++;
                }
                foreach ($data as $v) {
                    foreach ($tempData as $k2 => $v2) {
                        $tempData[$k2][$v2['tempDataKey']][] = $v[$v2['tempSortKey']];
                    }
                }
                $arguments = array();
                foreach ($tempData as $v) {
                    $arguments[] = $v[$v['tempDataKey']];
                    $arguments[] = (strtolower($v['tempSortBy']) == 'desc') ? SORT_DESC : SORT_ASC;
                }
                $arguments[] = & $data; //����ط��Ƚ����⣬��Ҫ��������"&"
                if (count($sortData) > 0 && !empty($arguments)) {
                    $execString = 'array_multisort(';
                    foreach ($arguments as $k => $v) {
                        $execString .= "\$arguments[$k],";
                    }

                    $execString = substr($execString, 0, strlen($execString) - 1);
                    $execString .= ');';
                    eval($execString);
                }
                //call_user_func_array('array_multisort',$arguments);
            }
            return $data;
        }
        return false;
    }

    /**
     * ��������Ԫ��
     */
    static function insertArray($data, $pos, $value)
    {
        if ($data) {
            if (is_array($pos)) {
                foreach ($pos as $k => $v) {
                    $val = isset($value[$k]) ? $value[$k] : null;
                    $data = self::insertArray($data, $v, $val);
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
    static function mergeArray(array $arr1, array $arr2, $deep = true)
    {
        $result = array();
        if ($deep) { //��ȱ���
            foreach ($arr1 as $k => $v) {
                if (isset($arr2[$k])) {
                    if (is_array($v)) {
                        $result[$k] = self::mergeArray($v, $arr2[$k]);
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
     * @param $splitNum
     * @return array
     */
    static function splitArray(array $data, $splitNum)
    {
        if (!empty($data) && ($splitNum > 0) && is_array($data)) {
            $result = array();
            $splitNum = (int)$splitNum;
            $arrNum = ceil(count($data) / $splitNum);
            while ($arrNum > 0) {
                $result[] = array_splice($data, 0, $splitNum);
                $arrNum--;
            }
            return $result;
        }
        return $data;
    }

    static function diffArray(array $arr1, array $arr2)
    {

    }

    static function randArray(array $data){
        if(!empty($data)){
            $key = array_rand($data);
            return $data[$key];
        }
        return null;
    }

    static function replaceArrayKey(array $data, $ori_key, $replace_key, $save = true, $deep = true){
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
                            $data[$k] = self::replaceArrayKey($v,$ori_key,$replace_key,$save,$deep);
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

    static function getOneNode(array $data, $row, $col)
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
            return $result;
        } else {
            return false;
        }
    }
}
