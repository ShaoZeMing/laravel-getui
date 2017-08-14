<?php

namespace GeTui\App\Repositories;
use DB;
use Illuminate\Support\Facades\Log;
use Validator;
use GeTui\App\Entities\MessagePush;
use GeTui\App\Validators\MessagePushValidator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
class MessagePushRepository extends BaseRepository
{
    //
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return MessagePush::class;
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
            'uid',
            'status',
            'type',
            'created_at')
            ->where('status', 1)
            ->where('is_sent_all', 0);
        if (isset($where['uid'])) {
            $query = $query->orWhere('uid', $where['uid']);
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
    public function selectData()
    {
        $data = $this->makeModel()->where('is_push_type', 1)->orderBy('created_at', 'desc')->paginate(config('page.admin_page', 10));

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
                '未注册册用户',
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
    public function insertData($data, $userPushOption, $message)
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
        try{
            $data['msg_id'] = $message->create($data)->id;
        }catch(\Exception $ex) {
            Log::info('r=MessagePushRepository f=insertData msg=消息入messages库异常');
            return false;
        }

        if ($data['msg_id']) {
            /*发送目标是否含有消息中心*/
            return $this->insertMessagePush($uidArray, $data);
        }

    }


    /**
     * 将push消息插入message_push表
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function insertMessagePush($uidArray, $data)
    {
        /*是否全员发送*/
        $result = [
            'title' => $data['title'],
            'content' => $data['content'],
            'origin_url' => $data['origin_url'],
            'short_url' => $data['short_url'],
            'status' => $data['status'],
            'type' => $data['type'],
            'is_sent_all' => $data['is_sent_all'],
            'video_id' => $data['video_id'],
            'lesson_id' => $data['lesson_id'],
            'msg_id' => $data['msg_id'],
        ];


        if ($data['is_sent_all'] == 1) {
            $result['uid'] = 0;
            return $this->create($result);
        }

        //防止筛选用户过多，sql语句过长。
        try{
        $offset=0;
        $length =1000;
        $count = count($uidArray);
        while($offset<$count){
            $uids= array_slice($uidArray,$offset,$length);
            //整合数据批量插入
            $datas = [];
            foreach ($uids as $v) {
                //未注册用户不入消息中心
                if ($v == 0)continue;
                $result['uid'] = (int)$v;
                $datas[] = $result;
            }
            $this->makeModel()->newQuery()->insert($datas);
            $offset += $length;
        }
         return true;
        }catch(\Exception $ex) {
            Log::info('r=MessagePushRepository f=insertMessagePush msg=循环批量消息中心入库异常');
            return false;
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
