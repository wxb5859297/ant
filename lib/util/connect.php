<?php
/**
 * 连接方法集合
 * @author wuxiabing
 * @date 13-8-7 下午9:50
 */

class lib_util_connect
{

    /**
     * 请求方法
     * @param  $url
     * @param string $request_type
     * @param array $param
     * @param int $timeout
     * @return array
     */
    static function curlRequest($url, $request_type = 'get', $param = array(), $timeout = 10)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (strtolower($request_type) == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1); //启用POST提交
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param); //设置POST提交的字符串
        }
        $result_content = curl_exec($ch);
        $result_code = curl_errno($ch);
        if ($result_code) {
            $result_content = 'Curl errno: ' . $result_code .
                '; Curl error: ' . curl_error($ch) .
                '; Curl time: ' . curl_getinfo($ch, CURLINFO_TOTAL_TIME) .
                '; Http code: ' . curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        curl_close($ch);
        return array('result_code' => $result_code, 'result_content' => $result_content);
    }

    /**windows下面无法使用，curl_multi_select有bug,只能在linux下使用
     * 多url并发请求
     * @param array $urls
     * @param int $timeout
     * @return array
     */
    static function curlMultiRequest(array $urls, $timeout = 3)
    {
        $running = null;
        $result = $ch = array();
        $mh = curl_multi_init();
        foreach ($urls as $k => $url) {
            $ch[$k] = curl_init();
            curl_setopt_array($ch[$k], array(
                CURLOPT_URL => $url,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => $timeout,
            ));
            curl_multi_add_handle($mh, $ch[$k]);
        }
        // 执行批处理句柄
        do {
            $mrc = curl_multi_exec($mh, $running);
        } while (CURLM_CALL_MULTI_PERFORM == $mrc);

        while ($running && $mrc == CURLM_OK) {
            if (curl_multi_select($mh, 0.5) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $running);
                } while (CURLM_CALL_MULTI_PERFORM == $mrc);
            }
        }

        //取数据
        foreach ($urls as $k => $url) {
            $result_code = curl_errno($ch[$k]);
            if ($result_code) {
                $result[$k] = array(
                    'return_code' => $result_code,
                    'return_content' => 'Curl errno: ' . $result_code .
                        '; Curl error: ' . curl_error($ch[$k]) .
                        '; Curl time: ' . curl_getinfo($ch[$k], CURLINFO_TOTAL_TIME) .
                        '; Http code: ' . curl_getinfo($ch[$k], CURLINFO_HTTP_CODE)
                );
            } else {
                $result[$k] = array(
                    'return_code' => $result_code,
                    'return_content' => curl_multi_getcontent($ch[$k])
                );
            }
            curl_multi_remove_handle($mh, $ch[$k]);
            curl_close($ch[$k]);
        }
        curl_multi_close($mh);
        return $result;
    }

    /**
     * @param $host
     * @param int $port
     * @param $path
     * @param $data
     * @return array|bool
     */
    static function getSockopenData($host, $port = 443, $path, $data)
    {
        $fp = fsockopen('ssl://' . $host, $port, $errno, $errstr, 10);
        if (!$fp) return false;
        fputs($fp, "POST " . $path . " HTTP/1.0\n");
        fputs($fp, "Accept-Language: zh-tw\n");
        fputs($fp, "Content-Type: application/x-www-form-urlencoded\n");
        fputs($fp, "Host: " . $host . "\n");
        fputs($fp, "Content-Length: " . strlen($data) . "\n");
        fputs($fp, "\n");
        fputs($fp, "$data");
        $i = 0;
        $return_data = array();
        while (!feof($fp)) {
            $return_data[$i++] = trim(fgets($fp, 128));
        }
        fclose($fp);
        return $return_data;
    }
}
