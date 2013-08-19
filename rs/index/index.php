<?php

class rs_index_index extends antc
{
    public function exec(antr $r)
    {
        $this->noView();
        $index = 'E:\workspace\stock_index.xml';
        $onto = 'E:\workspace\stock_onto.xml';
        $index_arr = lib_util_xml::readXmlToArray($index);
        foreach ($index_arr['index'] as $k => $v) {
            $temp = array();
            if (isset($v['params']['param']) && is_array($v['params']['param'])) {
                foreach ($v['params']['param'] as $k2 => $v2) {
                    if (isset($v2['@attributes'])) {
                        $temp[trim($v2['@attributes']['title'])] = $v2['@attributes'];
                    } else {
                        $temp[trim($v2['title'])] = $v2;
                    }
                }
            }
            $index_result[trim($v['@attributes']['title'])] = array(
                'attr' => $v['@attributes'],
                'params' => $temp,
            );
        }
        $onto_arr = lib_util_xml::readXmlToArray($onto);
        foreach ($onto_arr['class'] as $k => $v) {
            if (isset($v['prop'][0])) {
                if ($v['prop'][0]['@attributes']['label'][0] == '_') {
                    unset($v['prop'][0]);
                }
            }
            $temp = array();
            foreach ($v['prop'] as $k2 => $v2) {
                if (isset($v2['@attributes'])) {
                    $temp[trim($v2['@attributes']['label'])] = $v2['@attributes'];
                } else {
                    $temp[] = $v2;
                }
            }
            $onto_result[trim($v['@attributes']['label'])] = array(
                'attr' => $v['@attributes'],
                'params' => $temp,
            );
        }


        $key = array();
        foreach ($index_result as $k => $v) {
            if ($onto_value = $onto_result[$k]) {
                $v['attr']['data_src'] = $onto_value['attr']['data_src'];
                foreach ($v['params'] as $pk => $p) {
                    if (isset($onto_value['params'][$pk])) {
                        $index_result[$k]['params'][$pk]['pubName'] = $onto_value['params'][$pk]['type'];
                        $key[$onto_value['params'][$pk]['type']] = 1;
                    } else {
                        $index_result[$k]['params'][$pk]['pubName'] = 'DATE';
                    }
                }
            }
        }
        echo '<pre>';
        var_dump($key,$index_result);
        echo '</pre>';
        die;
    }
}