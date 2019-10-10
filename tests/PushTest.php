<?php
/**
 *  TestSms.php
 *
 * @author szm19920426@gmail.com
 * $Id: TestSms.php 2017-08-17 上午10:08 $
 */

namespace ShaoZeMing\GeTui\Test;
require_once dirname(__FILE__) . '/../src/getui/IGt.Push.php';

use PHPUnit\Framework\TestCase;
use ShaoZeMing\GeTui\GeTuiService;


class PushTest extends TestCase
{
    protected $instance;

    public function setUp()
    {

        $file =  dirname(__DIR__) .'/src/config/getui.php';
        $config = include($file);
        $this->instance = new GeTuiService($config);
    }


    public function testPushManager()
    {
        $this->assertInstanceOf(GeTuiService::class, $this->instance);
    }


    public function testPush()
    {
        echo PHP_EOL."发送push 中....".PHP_EOL;
        try {
            $deviceId = '2e682657977c5c616481ae76088b033d';
            $title = '噪音好大啊，啊，啊，啊啊，';
            $content = '你好呀您负责的的工单已经追加元';

            $data = [
                'type' => 9,
                'title' => $title,
                'content' => $content,
            ];

            $getuiResponse = $this->instance->push($deviceId, $data);
//            $getuiResponse = $this->instance->pushToApp( $data);
            echo json_encode($getuiResponse).PHP_EOL;
//            $this->assertContains('ok',$getuiResponse,'不成功');
//            return $getuiResponse;
        } catch (\Exception $e) {


            $err = "Error : 错误：" . $e->getMessage();
            echo $err.PHP_EOL;

        }
    }
}
