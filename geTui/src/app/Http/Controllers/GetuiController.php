<?php

namespace  GeTui\App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;
use GeTui\App\Repositories\MessageRepository;
use GeTui\App\Validators\MessageValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

require_once  base_path(). '/packages/geTui/geTui/getui/IGt.Push.php';

trait GeTuiController 
{
	use AuthorizesRequests, ValidatesRequests;
    public $getui;
	public function __construct()
    {
        $driver = config('getui.driver');
        $params = config('getui.'.$driver);
        $this->getui = new \IGeTui($params['gt_domainurl'], $params['gt_appkey'], $params['gt_mastersecret'], $ssl = NULL);
    }

    public function pushMsgToApp($transContent, $content, $title, $logoUrl)
    {
        //消息模版：
        // 1.TransmissionTemplate:透传功能模板
        // 2.LinkTemplate:通知打开链接功能模板
        // 3.NotificationTemplate：通知透传功能模板
        // 4.NotyPopLoadTemplate：通知弹框下载功能模板

        $template = $this->getAndroidNotificationTemplateDemo($transContent, $content, $title, $logoUrl);
        //个推信息体
        //基于应用消息体
        $message = new IGtAppMessage();
        $message->set_isOffline(true);
        $message->set_offlineExpireTime(3600*12*1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
        $message->set_data($template);
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        $message->set_speed(100);// 设置群推接口的推送速度，单位为条/秒，例如填写100，则为100条/秒。仅对指定应用群推接口有效。
        $message->set_appIdList(array(GT_APPID));
        $rep = $this->getui->pushMessageToApp($message);
        return $rep;
    }


    public function getAndroidNotificationTemplateDemo($transContent, $content, $title, $logoUrl)
    {
        //$template =  new IGtNotificationTemplate();
        $template = new IGtTransmissionTemplate();
        $template->set_appId(GT_APPID);              //应用appid
        $template->set_appkey(GT_APPKEY);            //应用appkey
        $template->set_transmissionType(2);          //透传消息类型
        $template->set_transmissionContent($transContent);//透传内容
        // $template->set_title($title);                  //通知栏标题
        // $template->set_text($content);     //通知栏内容
        // $template->set_logo("logo.png"); // 通知栏logo
        // if ($logoUrl) {
        //     $template->set_logoURL($logoUrl); //通知栏logo链接
        // }                   
        // $template->set_isRing(true);                   //是否响铃
        // $template->set_isVibrate(true);                //是否震动
        // $template->set_isClearable(true);              //通知栏是否可清除

        //设置通知定时展示时间，结束时间与开始时间相差需大于6分钟，消息推送后，客户端将在指定时间差内展示消息（误差6分钟）
        //$begin = "2015-02-28 15:26:22";
        //$end = "2015-02-28 15:31:24";
        //$template->set_duration($begin,$end);
        // iOS推送需要设置的pushInfo字段
        //$template->set_pushInfo($actionLocKey,$badge,$message,$sound,$payload,$locKey,$locArgs,$launchImage);
        $template->set_pushInfo($title, 1, $content,"","","","", $logoUrl);
        return $template;
    }

   

    public function pushMsgToIOSApp($deviceTokenList, $transContent, $content, $title, $logoUrl)
    {
        //多个用户推送接口   
        //putenv("needDetails=true");
        // $template = new IGtAPNTemplate();
        // $template->set_pushInfo($title, 1, $transContent, "", $content, "", "", "");
        $template = $this->getIOSTransmissionTemplateDemo($transContent, $content, $title, $logoUrl='');

        $listmessage = new IGtListMessage();
        $listmessage->set_data($template);
        $contentId = $this->getui->getAPNContentId(GT_APPID, $listmessage);
        $ret = $this->getui->pushAPNMessageToList(GT_APPID, $contentId, $deviceTokenList);
        return $ret;
    }

    public function pushToSignal($clientId, $transContent, $content, $title, $logoUrl='', $deviceOs='ios')
    {
        if ($deviceOs == 'ios') {
            $req = $this->pushToSignalIOS($clientId, $transContent, $content, $title, $logoUrl='');
        }else {
            $req = $this->pushToSignalAndroid($clientId, $transContent, $content, $title, $logoUrl='');
        }
        return $req;
    }
    public function getIOSTransmissionTemplateDemo($transContent, $content, $title, $logoUrl='')
    {
        $template =  new IGtTransmissionTemplate();
        $template->set_appId(GT_APPID);              //应用appid
        $template->set_appkey(GT_APPKEY);            //应用appkey
        $template->set_transmissionType(1);          //透传消息类型
        $template->set_transmissionContent($transContent);//透传内容

        $apn = new IGtAPNPayload();
        $alertmsg = new DictionaryAlertMsg();
        $alertmsg->body = $content;
//        IOS8.2 支持
        $alertmsg->title = $title;
        $apn->alertMsg = $alertmsg;
        $apn->badge = 1;
        $template->set_apnInfo($apn);
        return $template;
    }
    public function pushToSignalIOS($clientId, $transContent, $content, $title, $logoUrl='')
    {
       $template = $this->getIOSTransmissionTemplateDemo($transContent, $content, $title, $logoUrl='');
        //$template->set_pushInfo($title, 1, $transContent, "", $content, "", "", "");
         
        //单个用户推送接口
        $message = new IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);
        $ret = $this->getui->pushAPNMessageToSingle(GT_APPID, $clientId, $message);
        return $ret;
    }

    public function pushToSignalAndroid($clientId, $transContent, $content, $title, $logoUrl='')
    {
        $template = $this->IGtNotificationTemplateDemo($transContent, $content, $title, $logoUrl);
        $message = new IGtSingleMessage();
        $message->set_isOffline(true);//是否离线
        $message->set_offlineExpireTime(3600*12*1000);//离线时间
        $message->set_data($template);//设置推送消息类型
        $message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
        //接收方
        $target = new IGtTarget();
        $target->set_appId(GT_APPID);
        $target->set_clientId($clientId);
        $rep = $this->getui->pushMessageToSingle($message, $target);
        return $rep;
    }

    public function getPushResult($taskId)
    {
        $params = array();
        $url = 'http://sdk.open.api.igexin.com/apiex.htm';
        $params["action"] = "getPushMsgResult";
        $params["appkey"] = GT_APPKEY;
        $params["taskId"] = $taskId;
        $sign   = $this->createSign($params,GT_MASTERSECRET);
        $params["sign"] = $sign;
        $data   = json_encode($params);
        $result = $this->httpPost($url,$data);
        return $result;
    }
    public function createSign($params,$masterSecret)
    {
        $sign=$masterSecret;
        foreach ($params as $key => $val){
            if (isset($key) && isset($val) ){
                if(is_string($val) || is_numeric($val) ){ // 针对非 array object 对象进行sign
                    $sign .= $key . ($val); //urldecode
                }
            }
        }
        return md5($sign);
    }

    public function httpPost($url,$data)
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
}
