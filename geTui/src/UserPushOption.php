<?php

namespace  GeTui;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GeTui\App\Repositories\UserPushOptionApiRepository;
use Validator;

class UserPushOption
{
    public function __construct(UserPushOptionApiRepository $userPushOptionApiRepository)
    {
        $this->userPushOptionApiRepository = $userPushOptionApiRepository;
    }
    public function insertOrUpdateUserPushOption($data, $type='register', $rules=[], $messages=[])
    {
        if(!$rules) {
            $rules = [
                'uid' => 'required',
            ];
        }
        if(!$messages) {
            $messages = [
                'uid.required' => '用户id不能为空',
            ];
        }
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $output = [
                'error' => '20001',
                'msg'   => config('getui_errcode.20001'),
                'data'  => $errors
            ];
            Log::info('[t=MessagePushesController f=insertMessagePushes  msg=参数校验失败 data='.json_encode($errors).']');
            return json_encode($output);
            // throw new \Exception(config('errcode.20000'), 20000);
        }
        $where = [
            'uid' => $data['uid']
        ];
        $userPushId = $this->userPushOptionApiRepository->insertOrUpdate($where, $data, $type);
        $output = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => ['user_push_options_id' => $userPushId],
        ];
        return json_encode($output);
    }
    /**
    * 已经注册的用户app
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
        $results = $this->userPushOptionApiRepository->getUserAppsByOptions($options, $offset, $limit);
        $output = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => $results,
        ];
        return json_encode($output);
    }

    public function updateUserPushOption($uid, $updata)
    {
        try {
            Log::info('c=userPushOption c=updateUserPushOption data='.json_encode($updata));

            $results = $this->userPushOptionApiRepository->getInfoByUid($uid);
            $data    = $updata;
            if(isset($updata['amount'])) {
                $operate = isset($update['amount_operate']) ?  $update['amount_operate'] : '+';
                if($operate == '+') {
                    $data['total_amount'] = $results['total_amount'] + $updata['amount'];
                } elseif($operate == '-') {
                    $data['total_amount'] = $results['total_amount'] - $updata['amount'];
                }
                if(isset($data['total_amount']) && $data['total_amount']<0) {
                    Log::info('c=userPushOption c=updateUserPushOption msg=金额小于0');
                    $output = [
                        'error' => 20004,
                        'msg'   => config('getui_errcode.20004'),
                    ];
                    return json_encode($output);
                }
            }
            if(isset($updata['gold'])) {
                $operate = isset($update['gold_operate']) ?  $update['gold_operate'] : '+';
                if($operate == '+') {
                    $data['gold'] = $results['gold'] + $updata['gold'];
                    Log::info('c=userPushOption c=updateUserPushOption gold='.json_encode($data));
                } elseif($operate == '-') {
                    $data['gold'] = $results['gold'] - $updata['gold'];
                    Log::info('c=userPushOption c=updateUserPushOption gold='.json_encode($data));
                }
                if(isset($data['gold']) && $data['gold']<0) {
                    Log::info('c=userPushOption c=updateUserPushOption msg=积分小于0');
                    $output = [
                        'error' => 20005,
                        'msg'   => config('getui_errcode.20005'),
                    ];
                    return json_encode($output);
                }
            }
            if(isset($updata['buy_count'])) {
                $data['buy_count'] = $results['buy_count'] + 1;
                Log::info('c=userPushOption c=updateUserPushOption gold='.json_encode($data));
            }
            $where = [
                'id' => $results['id']
            ];
            Log::info('c=userPushOption c=updateUserPushOption gold='.json_encode($data));

            $flag = $this->userPushOptionApiRepository->insertOrUpdate($where, $data, 'update');
            if($flag) {
                $output = [
                    'error' => 0,
                    'msg'   => config('getui_errcode.0'),
                    'data'  => $results,
                ];
                return json_encode($output);
            } 
            $output = [
                'error' => 20003,
                'msg'   => config('getui_errcode.20003'),
                'data'  => $results,
            ];
            return json_encode($output);
        }catch(\Exception $ex) {
            Log::info('c=userPushOption c=updateUserPushOption msg='.$ex->getMessage());
            $output = [
                'error' => 20003,
                'msg'   => config('getui_errcode.20003'),
                'data'  => $results,
            ];
            return json_encode($output);
        }
    }
}
