<?php

namespace  GeTui;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GeTui\App\Repositories\UserAppApiRepository;
use Validator;

class UserApp
{
    public function __construct(UserAppApiRepository $userAppApiRepository)
    {
        $this->userAppApiRepository = $userAppApiRepository;
    }
    /**
    * 保持一个app登录
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function keepOnlyOneOnline($devId, $devOs, $uid)
    {
        $where = [
            'worker_id' => $uid,
            'is_logout' => 0
        ];
        $userApps = $this->userAppApiRepository->getUserAppByWhere($where);
        Log::info('c=UserApp f=keepOnlyOneOnline devId='.$devId.' devOs='.$devOs.' uid='.$uid);
        $userAppId = $this->userAppApiRepository->keepOnlyOneOnline($devId, $devOs, $uid);
        $output   = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => array_shift($userApps),
        ];
        return json_encode($output);
    }
   /**
    * 写入或者更新user_app
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function insertOrUpdateAppData($data, $type='register')
    {
        Log::info('c=UserApp f=insertOrUpdateAppData data='.json_encode($data));
        $data['dev_os'] = isset($data['dev_os']) ? strtolower($data['dev_os']) : '';
        $userAppId = $this->userAppApiRepository->insertOrUpdateData($data, $type);
        $output = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => ['user_app_id' => $userAppId],
        ];
        return json_encode($output);
    }
    /**
    * 写入user_app
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function insert($data)
    {
        Log::info('c=UserApp f=insertOrUpdateAppData data='.json_encode($data));
        $data['dev_os'] = isset($data['dev_os']) ? strtolower($data['dev_os']) : '';
        $userAppId = $this->userAppApiRepository->insert($data);
        $output = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => ['user_app_id' => $userAppId],
        ];
        return json_encode($output);
    }

    /**
    * user app是否已经注册
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  mixed Request $request
    *
    * @return mixed
    */
    public function getUserApp($devId, $uid)
    {
        $results = $this->userAppApiRepository->getUserAppByDevId($devId, $uid);
        $output  = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => $results,
        ];
        return json_encode($output);
    }
    /**
    * 指定用户user app
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  string   $uid
    * @param  int       $offset
    * @param  int       $limit
    *
    * @return mixed
    */
    public function getUserAppByUid($uid, $offset=0, $limit=10)
    {
        $uids     = is_array($uid) ? $uid : explode(',', $uid);
        $userApps = $this->userAppApiRepository->getUserAppByUid($uids, $offset, $limit);
        $output   = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => $userApps,
        ];
        return json_encode($output);
    }
    /**
    * 未注册user app
    *
    * @author zhangjun@xiaobaiyoupin.com
    *
    * @param  int   $offset
    * @param  int   $limit
    *
    * @return mixed
    */
    public function getUnregisterApp($where, $offset=0, $limit=10)
    {
        Log::info('c=userApp f=getUnregisterApp where='.json_encode($where));
        $userApps = $this->userAppApiRepository->getUserAppByWhere($where, $offset, $limit);
        $output   = [
            'error' => 0,
            'msg'   => config('getui_errcode.0'),
            'data'  => $userApps,
        ];
        return json_encode($output);
    }

}
