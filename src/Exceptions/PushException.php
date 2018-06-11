<?php

namespace ShaoZeMing\Push\Exceptions;
/**
 *  SmsException.php
 *
 * @author szm19920426@gmail.com
 * $Id: SmsException.php 2017-08-16 上午11:05 $
 */
class PushException extends \Exception
{
    protected static $errorMsgs = [
        '1000' => '系统内部错误',
        '1001' => '只支持 HTTP Post 方法',
        '1002' => '缺少了必须的参数',
        '1003' => '参数值不合法',
        '1004' => '验证失败',
        '1005' => '消息体太大',
        '1008' => 'app_key参数非法',
        '1009' => '推送对象中有不支持的key',
        '1011' => '没有满足条件的推送目标',
        '1020' => '只支持 HTTPS 请求',
        '1030' => '内部服务超时',
        '2002' => 'API调用频率超出该应用的限制',
        '2003' => '该应用appkey已被限制调用 API',
        '2004' => '无权限执行当前操作',
        '2005' => '信息发送量超出合理范围。',
    ];
    public function __construct($code)
    {
        parent::__construct(self::$errorMsgs[$code], $code);
    }
}
 