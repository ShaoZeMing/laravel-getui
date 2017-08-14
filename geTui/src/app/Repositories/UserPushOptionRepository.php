<?php

namespace GeTui\App\Repositories;

use DB;
use GeTui\App\Entities\UserPushOption;
use Validator;
use GeTui\App\Entities\MessagePush;
use GeTui\App\Validators\MessagePushValidator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;


class UserPushOptionRepository extends BaseRepository
{
    //
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserPushOption::class;
    }

//    /**
//     * Specify Validator class name
//     *
//     * @return mixed
//     */
//    public function validator()
//    {
//        return MessagePushValidator::class;
//    }


    /**
     * 用户push通知时筛选
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function searchUserPushOption($request)
    {

        $where = [
            isset($request['locate']) ? ['locate', '=', $request['locate']] : [],        //搜索课程
            isset($request['app_type']) && $request['app_type'] == 'android' ? ['is_android_app', '=', 1] : [],    //搜索Android
            isset($request['app_type']) && $request['app_type'] == 'ios' ? ['is_ios_app', '=', 1] : [],        //搜索ios
            isset($request['is_register']) && $request['is_register'] == 0 ? ['uid', '=', 0] : [],        //名称
            isset($request['is_register']) && $request['is_register'] == 1 ? ['uid', '!=', 0] : [],        //名称
            isset($request['loginBegin']) ? ['logined_time', '>=', $request['loginBegin']] : [],
            isset($request['loginEnd']) ? ['logined_time', '<', $request['loginEnd'] . ' 23:59:59'] : [],
            isset($request['registerBegin']) ? ['registed_time', '>=', $request['registerBegin']] : [],
            isset($request['registerEnd']) ? ['registed_time', '<', $request['registerEnd'] . ' 23:59:59'] : [],
            isset($request['moneyMin']) ? ['total_amount', '>=', changeMoneyToFen($request['moneyMin'])] : [],
            isset($request['moneyMax']) ? ['total_amount', '<', changeMoneyToFen($request['moneyMax'])] : [],
        ];
        $where = array_filter($where);  //过滤空值条件&&重新索引
        $data['ids'] = array_flatten($this->makeModel()->where($where)->select('uid')->get()->toArray());  //利用条件查找数据并分页
        $data['number']=count($data['ids']);     //统计数据 分页序号
        $data['data'] = $this->makeModel()->where($where)->paginate(config('page.admin_page', 10));  //利用条件查找数据并分页
        return $data;

    }



    /**
     * 用户消息筛选
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function newsSearchUserPushOption($request)
    {

        $where = [
            ['uid', '!=', 0],        //搜索全部注册用户
            isset($request['locate']) ? ['locate', '=', $request['locate']] : [],        //搜索课程
            isset($request['app_type']) && $request['app_type'] == 'android' ? ['is_android_app', '=', 1] : [],    //搜索Android
            isset($request['app_type']) && $request['app_type'] == 'ios' ? ['is_ios_app', '=', 1] : [],        //搜索ios
            isset($request['loginBegin']) ? ['logined_time', '>=', $request['loginBegin']] : [],
            isset($request['loginEnd']) ? ['logined_time', '<', $request['loginEnd'] . ' 23:59:59'] : [],
            isset($request['registerBegin']) ? ['registed_time', '>=', $request['registerBegin']] : [],
            isset($request['registerEnd']) ? ['registed_time', '<', $request['registerEnd'] . ' 23:59:59'] : [],
            isset($request['moneyMin']) ? ['total_amount', '>=', changeMoneyToFen($request['moneyMin'])] : [],
            isset($request['moneyMax']) ? ['total_amount', '<', changeMoneyToFen($request['moneyMax'])] : [],
        ];
        $where = array_filter($where);  //过滤空值条件&&重新索引
        $data['ids'] = array_flatten($this->makeModel()->where($where)->select('uid')->get()->toArray());  //利用条件查找数据并分页
        $data['data'] = $this->makeModel()->where($where)->paginate(config('page.admin_page', 10));  //利用条件查找数据并分页
        return $data;

    }

}
