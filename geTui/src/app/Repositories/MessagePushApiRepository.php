<?php

namespace GeTui\App\Repositories;

use DB;
use Validator;
use GeTui\App\Entities\MessagePush;
// use GeTui\App\Validators\MessagePushValidator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;


class MessagePushApiRepository extends BaseRepository
{
    //
    protected $_objType =[
            '0' => 'text',
            '1' => 'html',
            '2' => 'video',
            '3' => 'lesson',
        ];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return MessagePush::class;
    }

    /**
     * Specify Validator class name
     *
     * @return mixed
     */
    public function validator()
    {
        // return MessagePushValidator::class;
    }


    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function insert($data)
    {
        $data = $this->makeModel()->fill($data)->toArray();
        return $this->makeModel()->newQuery()->insertGetId($data);
    }

    public function getList($where=[], $offset=0, $limit=10)
    {
        $query = $this->makeModel()->newQuery()->select(
                        'id',
                        'content',
                        'title',
                        'video_id',
                        'lesson_id',
                        'origin_url',
                        'short_url',
                        'uid',
                        'status',
                        'type as origin_type',
                        'created_at',
                        'updated_at'
                        )
                    ->where('status', 1);
        if(isset($where['uid'])) {
            $query = $query->where(function ($query) use ($where){
                $query->where('is_sent_all', '=', 1);
                $query->orWhere('uid', '=', $where['uid']);
            });
        } else {
            $query = $query->where('is_sent_all', 1);
        }

        $results = $query->offset($offset)
                        ->limit($limit)
                        ->orderBy('updated_at', 'desc')
                        ->orderBy('id', 'desc')
                        ->get()
                        ->toArray();
        if($results) {
            foreach($results as &$result) {
                $result['type'] = isset($this->_objType[$result['origin_type']]) ? $this->_objType[$result['origin_type']] : 'text';
            }
        }
        return $results;
    }
    public function getMaxId($where=[], $offset=0, $limit=1)
    {
        $query = $this->makeModel()->newQuery()->select(
                        'id'
                        )
                    ->where('status', 1);
        if(isset($where['uid'])) {
            $query = $query->where(function ($query) use ($where){
                $query->where('is_sent_all', '=', 1);
                $query->orWhere('uid', '=', $where['uid']);
            });
        } else {
            $query = $query->where('is_sent_all', 1);
        }

        $result = $query->offset($offset)
                        ->limit($limit)
                        ->get()
                        ->toArray();
        return $result;
    }
    /**
     * 更新状态为1 已发送
     *
     * @author zhangjun@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function updateStatusByMsgId($msgId)
    {
        try{
            $updata = [
                'status'     => 1,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            return $this->makeModel()->newQuery()->where('msg_id', $msgId)->update($updata);
        }catch(\Exception $ex) {
            return false;
        }
    }
}
