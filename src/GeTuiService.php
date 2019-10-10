<?php

namespace ShaoZeMing\GeTui;
use Illuminate\Support\Collection;

//use Illuminate\Support\Facades\Log;


require_once dirname(__FILE__) . '/getui/IGt.Push.php';

class GeTuiService implements PushInterface
{
    // use AuthorizesRequests, ValidatesRequests;

    const HOST = 'http://sdk.open.api.igexin.com/apiex.htm';  //http的域名
    /**
     * @var Collection
     */
    protected $config;
    protected $gt_appid;
    protected $gt_appkey;
    protected $gt_appsecret;
    protected $gt_mastersecret;

    /**
     * @var array
     */
    protected $gateways = [];


    public function __construct(array $config = null)
    {
        if(!$config){
           $config =  include(__DIR__.'/config/getui.php');
        }

        $this->config = $config;

        $appEnv = $this->config["app_env"];
        $client = $this->config["default_client"];
        $config = $this->config[$appEnv][$client];
        $this->obj = new \IGeTui($config['gt_domainurl'], $config['gt_appkey'], $config['gt_mastersecret']);
        $this->gt_appid = $config['gt_appid'];
        $this->gt_appkey = $config['gt_appkey'];
        $this->gt_appsecret = $config['gt_appsecret'];
        $this->gt_mastersecret = $config['gt_mastersecret'];
    }



    public function toClient($client = null)
    {
        $appEnv = $this->config["app_env"];
        if (empty($client)) {
            $client = $this->config["default_client"];
        }
        $config = $this->config[$appEnv][$client];
        $this->obj = new \IGeTui($config['gt_domainurl'], $config['gt_appkey'], $config['gt_mastersecret']);
        $this->gt_appid = $config['gt_appid'];
        $this->gt_appkey = $config['gt_appkey'];
        $this->gt_appsecret = $config['gt_appsecret'];
        $this->gt_mastersecret = $config['gt_mastersecret'];
        return $this;
    }




    public function getPushResult($taskId)
    {
        $params = array();
        $url = 'http://sdk.open.api.igexin.com/apiex.htm';
        $params["action"] = "getPushMsgResult";
        $params["appkey"] = $this->gt_appkey;
        $params["taskId"] = $taskId;
        $sign = $this->createSign($params, $this->gt_mastersecret);
        $params["sign"] = $sign;
        $data = json_encode($params);
        $result = $this->httpPost($url, $data);
        return $result;
    }

    public function createSign($params, $masterSecret)
    {
        $sign = $masterSecret;
        foreach ($params as $key => $val) {
            if (isset($key) && isset($val)) {
                if (is_string($val) || is_numeric($val)) { // 针对非 array object 对象进行sign
                    $sign .= $key . ($val); //urldecode
                }
            }
        }
        return md5($sign);
    }

    public function httpPost($url, $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, 'GeTui PHP/1.0');
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }


    /**
     * 推送单个或多个用户
     * @param array|string $deviceId
     * @param array $data
     * @param string $function 数据转换编码函数
     *
     * @return Message
     * @throws \Exception
     */
    public function push($deviceId, array $data, $isNotice = true, $function = 'json_encode')
    {
        if (empty($deviceId)) {
            throw new \Exception('device_id not empty');
        }

        if (!isset($data['content']) || !isset($data['title'])) {
            throw new \Exception('content and title not empty');
        }
        $shortUrl = isset($data['url']) ? $data['url'] : '';
        $message = new Message();
        $message->setContent($data['content']);
        $content = $message->getContent();

        $message->setTitle($data['title']);
        $title = $message->getTitle();
        $transContent = $function($data);

        if (is_array($deviceId)) {
            $result = $this->pushMessageToList($deviceId, $transContent, $content, $title,$isNotice, $shortUrl);

        } else {
            $result = $this->pushMessageToSingle($deviceId, $transContent, $content, $title, $isNotice, $shortUrl);

        }
        return $result;


        //$2y$10$a6RR/UxbEYbmqfZi6zTiguPLV7cI.WtV7c/0.9nXeaSmf549VuDWe

        //$2y$10$a6RR/UxbEYbmqfZi6zTiguPLV7cI.WtV7c/0.9nXeaSmf549VuDWe


        //$2y$10$P7xOUnjy.AAzBY7QMxJqW.vDAai8dRmxSR3tmzlQ5HjcwVNRJNsAW    123123
    }


    /**
     * 发送给这个APP所有用户
     *
     * @param array $data
     * @param string $function
     *
     * @return Message
     * @throws \Exception
     */
    public function pushToApp(array $data,$isNotice=true, $function = 'json_encode')
    {

        if (!isset($data['content']) || !isset($data['title'])) {
            throw new \Exception('content and title not empty');
        }

        $message = new Message();
        $message->setContent($data['content']);
        $content = $message->getContent();
        $message->setTitle($data['title']);
        $title = $message->getTitle();

        $transContent = $function($data);
        $result = $this->pushMessageToApp($transContent, $content, $title ,$isNotice);
        return $result;
    }




    //
//服务端推送接口，支持三个接口推送
//1.PushMessageToSingle接口：支持对单个用户进行推送
//2.PushMessageToList接口：支持对多个用户进行推送，建议为50个用户
//3.pushMessageToApp接口：对单个应用下的所有用户进行推送，可根据省份，标签，机型过滤推送
//

//单推接口案例
    function pushMessageToSingle($clientId, $transContent, $content, $title, $isNotice = true, $shortUrl = '')
    {
        //消息模版：
        $template = $this->getTemplate($content, $title, $transContent,$isNotice, $shortUrl);
        //个推信息体
        $message = new \IGtSingleMessage();
        $message->set_isOffline(true);//是否离线

        if (!$isNotice) {
            $message->set_offlineExpireTime(100 * 1000);//离线时间
        } else {
            $message->set_offlineExpireTime(3600 * 12 * 1000);//离线时间
        }
        $message->set_data($template);//设置推送消息类型
//	$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new \IGtTarget();
        $target->set_appId($this->gt_appid);
        $target->set_clientId($clientId);
//    $target->set_alias(Alias);

        try {
            $rep = $this->obj->pushMessageToSingle($message, $target);
            return $rep;
        } catch (\RequestException $e) {
            $requstId = $e->getRequestId();
            $rep = $this->obj->pushMessageToSingle($message, $target, $requstId);
            return $rep;
        }

    }

//多推接口案例
    function pushMessageToList($clientIds,$transContent,  $content, $title, $isNotice = true, $shortUrl = '')
    {
        putenv("gexin_pushList_needDetails=true");
        putenv("gexin_pushList_needAsync=true");
        //消息模版：
        $template = $this->getTemplate($content, $title, $transContent,$isNotice, $shortUrl);
        //个推信息体
        $message = new \IGtListMessage();
        $message->set_isOffline(true);//是否离线
        if (!$isNotice) {
            $message->set_offlineExpireTime(100 * 1000);//离线时间
        } else {
            $message->set_offlineExpireTime(3600 * 12 * 1000);//离线时间
        }
        $message->set_data($template);//设置推送消息类型
//    $message->set_PushNetWorkType(1);	//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
//    $contentId = $igt->getContentId($message);
        $contentId = $this->obj->getContentId($message, "toList任务别名功能");    //根据TaskId设置组名，支持下划线，中文，英文，数字

        //接收方1
        $targetList = [];
        foreach ($clientIds as $key => $clientId) {
            $target = new \IGtTarget();
            $target->set_appId($this->gt_appid);
            $target->set_clientId($clientId);
            $targetList[] = $target;
        }

//    $target1->set_alias(Alias);
        $rep = $this->obj->pushMessageToList($contentId, $targetList);
        return $rep;

    }


//群推接口案例
    function pushMessageToApp($transContent, $content, $title, $isNotice = true, $shortUrl = '')
    {
        $template = $this->getTemplate($content, $title, $transContent, $isNotice, $shortUrl);
        //个推信息体
        //基于应用消息体
        $message = new \IGtAppMessage();
        $message->set_isOffline(true);

        if (!$isNotice) {
            $message->set_offlineExpireTime(100 * 1000);//离线时间
        } else {
            $message->set_offlineExpireTime(3600 * 12 * 1000);//离线时间
        }
        $message->set_data($template);

        $appIdList = array($this->gt_appid);
        $phoneTypeList = array('ANDROID');
        $provinceList = array('浙江');
        $tagList = array('haha');
        //用户属性
        //$age = array("0000", "0010");

        //$cdt = new AppConditions();
        // $cdt->addCondition(AppConditions::PHONE_TYPE, $phoneTypeList);
        // $cdt->addCondition(AppConditions::REGION, $provinceList);
        //$cdt->addCondition(AppConditions::TAG, $tagList);
        //$cdt->addCondition("age", $age);

        $message->set_appIdList($appIdList);
        //$message->set_conditions($cdt->getCondition());

        $rep = $this->obj->pushMessageToApp($message, "任务组名");

        return $rep;

    }


    protected function getTemplate($content, $title, $transContent, $isNotice = true, $shortUrl = '')
    {
//        switch ($type) {
//            case self::ALL:
//                return $this->IGtNotificationTemplateDemo($content, $title, $transContent);
//            case self::NOTICE:
//                return $this->IGtNotyPopLoadTemplateDemo($content, $title, $transContent);
//            case self::PENETRATE:
//                return $this->IGtTransmissionTemplateDemo($content, $title, $transContent);
//            case self::H5:
//                return $this->IGtLinkTemplateDemo($content, $title, $shortUrl);
//        }

        if ($isNotice) {
            return $this->IGtNotificationTemplateDemo($content, $title, $transContent);
        }
        return $this->IGtTransmissionTemplateDemo($content, $title, $transContent);
    }







//所有推送接口均支持四个消息模板，依次为通知弹框下载模板，通知链接模板，通知透传模板，透传模板
//注：IOS离线推送需通过APN进行转发，需填写pushInfo字段，目前仅不支持通知弹框下载功能


//推送通知
    function IGtNotyPopLoadTemplateDemo($content, $title, $transContent)
    {
        $template = new \IGtNotyPopLoadTemplate();

        $template->set_appId($this->gt_appid);//应用appid
        $template->set_appkey($this->gt_appkey);//应用appkey
        //通知栏
        $template->set_notyTitle($title);//通知栏标题
        $template->set_notyContent($content);//通知栏内容
        $template->set_notyIcon("");//通知栏logo
        $template->set_isBelled(true);//是否响铃
        $template->set_isVibrationed(true);//是否震动
        $template->set_isCleared(true);//通知栏是否可清除
        //弹框
        $template->set_popTitle($title);//弹框标题
        $template->set_popContent($transContent);//弹框内容
        $template->set_popImage("");//弹框图片
        $template->set_popButton1("下载");//左键
        $template->set_popButton2("取消");//右键
//        下载
        $template->set_loadIcon("");//弹框图片
        $template->set_loadTitle("地震速报下载");
        $template->set_loadUrl("http://dizhensubao.igexin.com/dl/com.ceic.apk");
        $template->set_isAutoInstall(false);
        $template->set_isActived(true);
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息

        return $template;
    }

    //推送通知链接模板
    function IGtLinkTemplateDemo($content, $title, $url)
    {
        $template = new \IGtLinkTemplate();
        $template->set_appId($this->gt_appid);//应用appid
        $template->set_appkey($this->gt_appkey);//应用appkey
        $template->set_title($title);//通知栏标题
        $template->set_text($content);//通知栏内容
        $template->set_logo("");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        $template->set_url($url);//打开连接地址
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }


    //透传模板
    function IGtTransmissionTemplateDemo($content, $title, $transContent)
    {
        $template = new \IGtTransmissionTemplate();
        $template->set_appId($this->gt_appid);//应用appid
        $template->set_appkey($this->gt_appkey);//应用appkey
        $template->set_transmissionType(2);//透传消息类型，自动打开应用
        $template->set_transmissionContent($transContent);//透传内容
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息

        //APN高级推送
        $apn = new \IGtAPNPayload();
        $alertmsg = new \DictionaryAlertMsg();
        $alertmsg->body = $content;
        $alertmsg->actionLocKey = "ActionLockey";
        $alertmsg->locKey = "LocKey";
        $alertmsg->locArgs = array("locargs");
        $alertmsg->launchImage = "launchimage";
//        IOS8.2 支持
        $alertmsg->title = $title;
        $alertmsg->titleLocKey = "TitleLocKey";
        $alertmsg->titleLocArgs = array("TitleLocArg");

        $apn->alertMsg = $alertmsg;
        $apn->badge = 1;
        $apn->sound = "";
        $apn->add_customMsg("payload", $transContent);
        $apn->contentAvailable = 1;
        $apn->category = "ACTIONABLE";
        $template->set_apnInfo($apn);
        return $template;
    }


    //通知+透传模板
    function IGtNotificationTemplateDemo($content, $title, $transContent)
    {
        $template = new \IGtNotificationTemplate();
        $template->set_appId($this->gt_appid);//应用appid
        $template->set_appkey($this->gt_appkey);//应用appkey
        $template->set_transmissionType(1);//透传消息类型
        $template->set_transmissionContent($transContent);//透传内容
        $template->set_title($title);//通知栏标题
        $template->set_text($content);//通知栏内容
        $template->set_logo("");//通知栏logo
        $template->set_isRing(true);//是否响铃
        $template->set_isVibrate(true);//是否震动
        $template->set_isClearable(true);//通知栏是否可清除
        //$template->set_duration(BEGINTIME,ENDTIME); //设置ANDROID客户端在此时间区间内展示消息
        return $template;
    }


}
