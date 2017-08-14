<?php

namespace GeTui\App\Repositories;

use DB;
use Illuminate\Support\Facades\Log;
use Validator;
use GeTui\App\Entities\Message;
use GeTui\App\Validators\MessageValidator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;


class MessageRepository extends BaseRepository
{
    //
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Message::class;
    }

//    /**
//     * Specify Validator class name
//     *
//     * @return mixed
//     */
//    public function validator()
//    {
//        return MessageValidator::class;
//    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function getUserByWhere($where)
    {
        $data = $this->makeModel()->newQuery()->select('id', 'name')
            ->where($where)
            ->get()
            ->toArray();
        return $data;
    }

    public function insert($data)
    {
        return $this->makeModel()->newQuery()->insertGetId($data);
    }

    public function getList($where = [], $offset = 0, $limit = 10)
    {
        $query = $this->makeModel()->newQuery()->select(
            'id',
            'content',
            'origin_url',
            'short_url',
            'options',
            'uid',
            'status',
            'type',
            'created_at'
        )->where('status', 1);
        if (isset($where['uid'])) {
            $query = $query->where('orders.uid', $where['uid']);
        }
        $result = $query->offset($offset)
            ->limit($limit)
            ->get()
            ->toArray();
        return $result;
    }


    /**
     * push消息查询处理
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function selectData($is_push_type=0)
    {
        $data = $this->makeModel()->where('is_push_type',$is_push_type)->orderBy('created_at', 'desc')->paginate(config('page.admin_page', 10));

        //数据处理
        foreach ($data as $v) {
            $options = json_decode($v->options);
            $is_sent_all = $v->is_sent_all;
            if ($is_sent_all == 1) {
                $v->options = '所有用户';
                continue;
            } elseif (isset($options->uid)) {
                $v->options = '指定用户ID:' . $options->uid;
                continue;

            }
            //替换指定条件
            $strOptions = '';
            foreach ((array)$options as $key => $val) {
                $strOptions .= $key . ':' . $val . '; ';
            }

            //查找数据
            $search = [
                'is_register:1',
                'is_register:0',
                'app_type',
                'ios',
                'android',
                'registerBegin',
                '; registerEnd:',
                'loginBegin',
                '; loginEnd:',
                'locate',
                'moneyMin',
                '; moneyMax:',
            ];
            //替换数据
            $match = [
                '注册用户',
                '未注册用户',
                'App类型',
                'iOS用户',
                'Android用户',
                '注册时间',
                '~',
                '最后登陆',
                '~',
                '地区',
                '消费金额范围(元)',
                '~',
            ];
            $v->options = str_replace($search, $match, $strOptions);
        }
        return $data;
    }




    /**
     * push消息插入处理
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function insertData($data, $userPushOption)
    {
        //生成短连接和信息类型
        $data['short_url'] = empty($data['origin_url']) ? '' : tinyurl($data['origin_url']);
        /*统计发送人数*/
        if ($data['is_sent_all'] == 0) {
            //全部用户
            $uidArray = array_flatten($userPushOption->makeModel()->all('uid')->toArray());            //获取所有用户一维id数组
            $data['send_count'] = count($uidArray);
            $data['is_sent_all'] = 1;
        } elseif ($data['is_sent_all'] == 2) {
            //指定用户
            $uidArray = $this->processingData($data['options']);  //获取指定用户id数组过滤并去重
            $data['send_count'] = count($uidArray);
            $data['is_sent_all'] = 0;
            $data['options'] = json_encode(['uid' => implode(',', $uidArray)]);
        } elseif ($data['is_sent_all'] == 1) {
            //筛选用户
            $uidArray = explode(',', $data['uids']);
            //判断是否筛选全部用户，优化处理
            if (empty(json_decode($data['options']))) {
                $data['is_sent_all'] = 1;
                $data['options'] = '';
            } else {
                $data['is_sent_all'] = 0;
            }
        }

        /*获取管理员id*/
        $data['admin_id'] = auth('admin')->user()->id;

        /*判断是否立即发送*/
        if ($data['status'] == 1) {
            $data['send_time'] = date('Y-m-d H:i:s');
        };
        /*插入消息获得消息ID*/
        $data['msg_id'] = $this->create($data)->id;

        if ($data['msg_id']) {
            if ($data['status'] == 1) {
                /*立即发送接口服务*/
                $result = [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'origin_url' => $data['origin_url'],
                    'short_url' => $data['short_url'],
                    'admin_id' => $data['admin_id'],
                    'status' => $data['status'],
                    'type' => $data['type'],
                    'options' => $data['options'],
                    'is_push_type' => $data['is_push_type'],
                    'send_count' => $data['send_count'],
                    'is_sent_all' => $data['is_sent_all'],
                    'send_time' => $data['send_time'],
                    'video_id' => $data['video_id'],
                    'lessonId' => $data['lesson_id'],
                ];
                app('MessageToApp')->sendPush($result, config('getui.push_flag'));
            }
            return true;
        }

    }

    /**
     * 对指定用户数据进行处理
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */

    public function processingData($data)
    {
        $array = [];
        $uidArray = explode(',', $data);  //获取用户id数组并去重
        foreach ($uidArray as $v) {
            $array[] = (int)$v;
        }
        return array_filter(array_unique($array));

    }

}
