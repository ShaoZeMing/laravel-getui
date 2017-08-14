<?php

namespace  GeTui;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GeTui\App\Repositories\MessageApiRepository;
use GeTui\App\Repositories\MessagePushApiRepository;
use GeTui\GeTuiService;

require_once  base_path(). '/packages/geTui/geTui/getui/IGt.Push.php';

class PushMessageToApp
{
    protected $_objType =[
            '0' => 'logout',
            '1' => 'html',
            '2' => 'text',
        ];
	public function __construct(MessageApiRepository $messsageApiRepository,  MessagePushApiRepository $messagePushApiRepository)
    {
        $this->messsageApiRepository    = $messsageApiRepository;
        $this->messagePushApiRepository = $messagePushApiRepository;
    }

    /**
    * 发送push
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function sendPush($data, $sendFlag = fasle)
    {
        $shorturl = '';
        $content  = $data['content'];
        $title    = $data['title'];
        $options  = $data['options'];
        $type     = $data['type'];
        $lessonId = 0;
        $videoId  = 0;
        if($type == 1) {
            $shorturl = $data['short_url'];
            $objType  = $this->_objType[1];
        } elseif($type == 2) {
            $videoId = $data['video_id'];
            $objType = $this->_objType[2];
        } elseif($type == 3) {
            $lessonId = $data['lessonId'];
            $objType  = $this->_objType[3];
        } else {
            $objType  = $this->_objType[0];
        }

        $transContent = array(
                'title'     => $title,
                'content'   => $content,
                'obj_type'  => $objType,
                'url'       => $shorturl,
                'lesson_id' => $lessonId,
                'video_id'  => $videoId,
            );
        $transContent = json_encode($transContent);
        Log::info('c=pushMessageToApp f=sendPush transContent='.json_encode($transContent));
        if($options) {
            $this->sendPushToPart($transContent, $content, $title, $options, $sendFlag);
        } else {
            $this->sendPushToAll($transContent, $content, $title, $sendFlag);
        }
    }
    /**
    * 发送所有的用户
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function sendPushToAll($transContent, $content, $title, $sendFlag=false)
    {
        if($sendFlag) {
            $rep = app('GeTuiService')->pushMsgToApp($transContent, $content, $title);
            $content = '[c=pushMessageToApp/sendPushToAll] [msg=发送透传通知] [content='.$content.'][title='.$title.'][rep='.json_encode($rep).']';
            Log::info($content);

            return $this->analyzeResult($rep);
        } else {
            $content = '[c=pushMessageToApp/sendPushToAll] [msg=只打印log] [content='.$content.'][title='.$title.']';
            Log::info($content);

            return true;
        }
        Log::info($content);
    }
    /**
    * 按条件发送
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function sendPushToPart($transContent, $content, $title, $options, $sendFlag=false)
    {
        $limit  = 100;
        $offset = 0;
        do{
            $clientIds = $this->getUserApp($options, $offset, $limit);
            if(!$clientIds) {
                break;
            }
            $clientIds = array_unique($clientIds);
            if($sendFlag) {
                $req = app('GeTuiService')->pushMessageToList($clientIds, $transContent,  $content, $title, '');
                Log::info('c=pushMsgToApp f=sendPushToPart msg=发送 info='.json_encode($clientIds).'content='.$content.'title='.$title.'req='.json_encode($req));
            } else {
                Log::info('c=pushMsgToApp f=sendPushToPart msg=只打印log info='.json_encode($clientIds).'content='.$content.'title='.$title);

            }
            $offset += $limit;
        }while($clientIds);
    }

   /**
    * 找到需要发送的用户信息
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    is_register 1 是注册，0是未注册
    *
    * @return mixed
    */
    public function getUserApp($options, $offset, $limit)
    {
        $options = json_decode($options, true);
        $results = false;
        Log::info('c=pushMsgToApp f=getUserApp options='.json_encode($options));
        if(isset($options['is_register']) && $options['is_register'] == 0) {
            $results = $this->getUnregisterApps($options, $offset, $limit);
         }
        if(isset($options['uid'])) {
            $results = $this->getUidApps($options, $offset, $limit);
        }
        if(!isset($options['uid']) && ((isset($options['is_register']) && $options['is_register'] !=0) || !isset($options['is_register']))) {
            $results = $this->getUserApps($options, $offset, $limit);
        }
        if(!$results) {
            return [];
        }
        $clientIds = [];
        $results   = json_decode($results, true);
        $userApps  = isset($results['data']) ? $results['data'] : '' ;
        if(!$userApps){
            return false;
        }
        foreach($userApps as $key => $val) {
            $clientIds[] = $val['device_id'];
        }

        return $clientIds;
    }
    /**
    * 未注册
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    * is_register 1 是注册，0是未注册
    *
    * @return mixed
    */
    public function getUnregisterApps($options, $offset, $limit)
    {
        $where   = [
                'uid'       => 0,
            ];
        if(isset($options['app_type'])) {
            $where['device_os'] =   $options['app_type'];
        }
        Log::info('c=pushMsgToApp f=getUnregisterApps where='.json_encode($where));
        $results = app('UserApp')->getUnregisterApp($where, $offset, $limit);
        return $results;
    }

    /**
    * 已注册
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    * is_register 1 是注册，0是未注册
    *
    * @return mixed
    */
    public function getUserApps($options, $offset, $limit)
    {
        Log::info('c=pushMsgToApp f=getUserApps');
        $results = app('UserPushOption')->getUserApps($options, $offset, $limit);
        return $results;
    }
    /**
    * 指定用户
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    * is_register 1 是注册，0是未注册
    *
    * @return mixed
    */
    public function getUidApps($options, $offset, $limit)
    {
        Log::info('c=pushMsgToApp f=getUidApps');
        $results = app('UserApp')->getUserAppByUid($options['uid'], $offset, $limit);
        return $results;
    }

    /**
    * 解析结果 记录日志
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function analyzeResult($result)
    {
        if ($result['result'] == 'ok') {
            $status['sent_status'] = 1;
            //TODO 更新表 消息的状态
            return true;
        }
        return false;
    }
    /**
    * 更新消息状态
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function updateMessageStatus($msgId, $isSentAll=0)
    {
        try{
           return app('db')->transaction(function ($app) use ($msgId, $isSentAll) {
                $centerFlag = 1;
                $msgFlag    = $this->messsageApiRepository->updateStatusById($msgId);
                if($isSentAll != 1) {
                    $centerFlag = $this->messagePushApiRepository->updateStatusByMsgId($msgId);
                }
                Log::info('c=pushMsgToApp msgFlag='.$msgFlag.' centerFlag='.$centerFlag);

                if(!$msgFlag || !$centerFlag) {
                    throw new \Exception(config('gettui_errcode.20002'), 20002);
                }
                return true;
            });
       } catch(\Exception $ex) {
            Log::info('c=pushMsgToApp f=updateMessageStatus msg='.$ex->getMessage());
            return false;
       }
    }
    /**
    * 写入消息中心，暂时停用
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    // public function insertMessagePushes($params, $userIds)
    // {
    //     Log::info('c=pushMsgToApp f=insertMessagePushes msg=消息中心入库');
    //     if(!$userIds || !is_array($userIds)) {
    //         $params['uid'] = 0;
    //         $pushesMsgId   = $this->messagePushApiRepository->insert($params);
    //         return true;
    //     }
    //     foreach($userIds as $uid) {
    //         $params['uid'] = $uid;
    //         $pushesMsgId   = $this->messagePushApiRepository->insert($params);
    //     }
    // }

    public function getPushResult($taskId)
    {
        $result = app('GeTuiService')->getPushResult($taskId);
        Log::info('c=pushMsgToApp f=getPushResult result='.$result);
    }

    /**
    * 发送push
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function pushToOneDev($devId, $devOs, $data, $sendFlag = true)
    {
        $shorturl = '';
        $content  = isset($data['content']) ? $data['content'] : '';
        $title    = isset($data['title']) ? $data['title'] :'';
        $type     = isset($data['type']) ? $data['type'] : '';
        if($type == 1) {
            $shorturl = isset($data['short_url']) ? $data['short_url'] : '';
            $objType  = $this->_objType[1];
        } elseif($type == 2) {
            $objType = $this->_objType[2];
        } elseif($type == 3) {
            $objType  = $this->_objType[3];
        } else {
            $objType  = $this->_objType[0];
        }

        $transContent = array(
                'title'     => $title,
                'content'   => $content,
                'obj_type'  => $objType,
                'url'       => $shorturl,
            );
        $transContent = json_encode($transContent);
        $result = app('GeTuiService')->pushToSignal($devId, $transContent, $content, $title, $shortUrl='', $devOs);
        Log::info('c=pushMessageToApp f=sendPush transContent='.json_encode($transContent) . ' result'. json_encode($result));
        return $result;
    }
}
