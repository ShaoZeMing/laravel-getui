<?php

namespace  GeTui;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GeTui\App\Repositories\MessagePushApiRepository;
use Illuminate\Container\Container as Application;
use Validator;

class PushMessageToCenter
{
    protected $_msgCenterContent = [
        'register' => 
            [
                "content" => "欢迎来到学球帮，在这里，你可以看到专业高尔夫教练的教学视频，分析自己的击球动作，提高自己的高尔夫技能！", 
                "title" => "注册成功！"
            ],
        'buy_gold' => [
                'content' => "您已成功购买了【{1}】课程，使用了【{2}】积分。您可以在用户中心-已购课程中查看。享受高尔夫，享受生活！",   
                "title" => "购买成功！"
            ],
        'buy_money' => [
                'content' => "您已成功购买了【{1}】课程，使用了【{2}】元。您可以在用户中心-已购课程中查看。享受高尔夫，享受生活！",   
                "title" => "购买成功！"
            ],
        'gold' => [
                'content' => "恭喜您！您在学球帮获得【{1}】积分，积分可以兑换收费课程，更多分享，更多积分！",
                "title" => "恭喜您获得【{1}】积分！",
            ],
    ];
    public function __construct(MessagePushApiRepository $messagePushRepository)
    {
        $this->messagePushApiRepository = $messagePushRepository;

    }
    public function insertMessagePushes($uid, $type='register', $params=[], $rules=[], $messages=[])
    {
        $msgs    = $this->_msgCenterContent[$type];
        $content = $this->getCenterContent($msgs['content'], $params);
        $title   = $this->getCenterContent($msgs['title'], $params);
        $data  = [
            'uid'         => $uid,
            'title'       => $msgs['title'],
            'content'     => $content,
            'status'      => 1,
            'type'        => 0,
            'is_sent_all' => 0,
            'created_at'  => date('Y-m-d H:i:s', time()),
            'updated_at'  => date('Y-m-d H:i:s', time()),
        ];
        if(!$rules) {
            $rules = [
                'content'     => 'required',
                'type'        => 'required',
                'status'      => 'required',
                'is_sent_all' => 'required',
            ];
        }
        if(!$messages) {
            $messages = [
                'content.required'     => '消息内容不能为空',
                'type.required'        => '消息类型不能为空',
                'status.required'      => '消息状态不能为空',
                'is_sent_all.required' => '发送人群不能为空',
            ];
        }
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $output = [
                'error' => '20000',
                'msg'   => config('getui_errcode.20000'),
                'data'  => $errors
            ];
            Log::info('[t=MessagePushesController f=insertMessagePushes  msg=参数校验失败 data='.json_encode($errors).']');
            return json_encode($output);
            // throw new \Exception(config('errcode.20000'), 20000);
        }
        $msgPushId = $this->messagePushApiRepository->insert($data);
        $output = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => ['msg_push_id' => $msgPushId],
        ];
        return json_encode($output);
    }
    /**
    * 得到发送用户中心的消息内容
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @return mixed
    */
    public function getCenterContent($content, $params=[])
    {
        if($params) {
            foreach ($params as $key => $value) {
                $key += 1;
                $content = preg_replace("/\{$key\}/i", $value, $content);
            }
        }
        return $content;
    }

    public function getLastId($uid)
    {
        $where = [];
        if($uid) {
            $where['uid'] = $uid;
        }
        $msgPush = $this->messagePushApiRepository->getMaxId($where);
        $msgPushId = 0;
        if($msgPush) {
            $msgPushId = isset($msgPush[0]) ? $msgPush[0]['id'] : 0; 
        }
        $output = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => ['msg_push_id' => $msgPushId],
        ];
        return json_encode($output);
    }

}
