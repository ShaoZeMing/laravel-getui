<?php

namespace GeTui\App\Repositories;

use GeTui\App\Entities\UserPushOption;
use DB;
use Validator;
use GeTui\App\Entities\MessagePush;
use GeTui\App\Validators\MessagePushValidator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class UserPushOptionApiRepository extends BaseRepository
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
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function insertOrUpdate($where, $data, $type='register')
    {
        $data = $this->makeModel()->fill($data)->toArray();
        if($type == 'register') {
            $data['created_at'] = date('Y-m-d H:i:s', time());
        } else {
            $data['updated_at'] = date('Y-m-d H:i:s', time());
        }
        return $this->makeModel()->newQuery()->updateOrCreate($where, $data);
    }
    /**
     * 指定用户的app
     *
     * @author zhangjun@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function getUserAppsByOptions($options, $offset, $limit)
    {
        $whereSql = '';
        if(isset($options['loginBegin'])) {
            $loginBegin = $options['loginBegin'] . ' 00:00:00';
            $whereSql .= ' where logined_time>='."'{$loginBegin}'";
        }
        if(isset($options['loginEnd'])) {
            $loginEnd  = $options['loginEnd'] . ' 23:59:59';
            $whereSql .=   $whereSql  ? ' and upo.logined_time<'."'{$loginEnd}'" : '  where upo.logined_time<'."{$loginEnd}" ;
        }

        if(isset($options['registerBegin'])) {
            $regBegin  = $options['registerBegin'] . ' 00:00:00';
            $whereSql .=   $whereSql ? ' and upo.registed_time>='."'{$regBegin}'" : '  where upo.registed_time>='."{$regBegin}" ;
        }

        if(isset($options['registerEnd'])) {
            $regEnd    = $options['registerEnd'] . ' 23:59:59';
            $whereSql .=   $whereSql ? ' and upo.registed_time<'."'{$regEnd}'" : '  where upo.registed_time<'."{$regEnd}" ;
        }

        if(isset($options['app_type'])) {
            $appType   = strtolower($options['app_type']);
            $whereSql .=  $whereSql ? ' and ua.device_os='."'{$appType}'" : ' where  ua.device_os='."'{$appType}'" ;
        }
        if(isset($options['moneyMin'])) {
            $amount    = $options['moneyMin'];
            $whereSql .=   $whereSql ? ' and upo.total_amount>= '."{$amount}" : '  where upo.total_amount>='."{$amount}" ;
        }
        if(isset($options['moneyMax'])) {
            $amount    = $options['moneyMax'];
            $whereSql .= $whereSql ? ' and upo.total_amount<'."{$amount}" : '  where  upo.total_amount<'."{$amount}" ;
        }
        if(isset($options['locate'])) {
            $locate    = $options['locate'];
            $whereSql .= $whereSql ? ' and upo.locate='."'{$locate}'" : '   where  upo.locate='."'{$locate}'" ;
        }
        $sql = "select
                    ua.device_id, ua.id, ua.uid
                from
                    user_apps as ua
                inner join
                    user_push_options as upo
                on
                    upo.uid = ua.uid
                $whereSql
                order by
                    ua.id desc
                limit
                    $offset , $limit
            ";
        $results = DB::select($sql);
        return $results;
    }

    public function getInfoByUid($uid)
    {
        $results = $this->makeModel()->newQuery()->select(
                                                    'id',
                                                    'total_amount',
                                                    'gold',
                                                    'locate',
                                                    'mobile',
                                                    'wechat_name',
                                                    'logined_time',
                                                    'registed_time',
                                                    'is_ios_app',
                                                    'is_android_app',
                                                    'uid',
                                                    'buy_count'
                                                    )
                                     ->where('uid', $uid)
                                     ->get()
                                     ->toArray();
        if($results) {
            return isset($results[0]) ? $results[0] : false;
        }
        return false;
    }

}
