<?php
// 生成短网址
if (!function_exists('generateShortUrl')) {
    /**
     * 生成短连接 百度
     * @author zhangjun@xiongmaojinfu.com
     * @param  mixed $url
     * @return mixed
     */
    function generateShortUrl($url)
    {
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://dwz.cn/create.php");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = array('url' => $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $strRes = curl_exec($ch);
        curl_close($ch);
        $arrResponse = json_decode($strRes, true);
        if ($arrResponse['status'] != 0) {
            /**错误处理*/
            //return iconv('UTF-8', 'GBK', $arrResponse['err_msg']);
            return false;
        }
        /** tinyurl */
        return $arrResponse['tinyurl'];
    }
}

if (!function_exists('tinyurl')) {

    function tinyurl($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url='.$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}


if (!function_exists('arrayFilterEmpty')) {
    /**
     * 过滤数组中的value===''|null 的键值
     * @author shangzeming@xiongmaojinfu.com
     * @param  mixed $array
     * @return mixed
     */
    function arrayFilterEmpty($arr)
    {
        $array = [];
        foreach ($arr as $k => $v) {
            if ($v === ''|| is_null($v) || $v=='all') {
                continue;
            }
            $array[$k] = $v;
        }
        return $array;
    }
}
if (!function_exists('changeMoneyToFen')) {

    function changeMoneyToFen($money)
    {
        return $money*100;
    }
}
if (!function_exists('changeMoneyToYuan')) {

    function changeMoneyToYuan($money)
    {
        return $money/100;
    }
}
