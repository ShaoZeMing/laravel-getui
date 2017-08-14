<?php

namespace GeTui\App\Repositories;

use DB;
use Validator;
use GeTui\App\Entities\Message;
use GeTui\App\Validators\MessageValidator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Log;

class MessageApiRepository extends BaseRepository
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
    /**
     * 得到未发送的消息
     * 
     * @author zhangjun@xiaobaiyoupin.com
     * 
     * @return mixed
     */
    public function getList($where = [], $offset = 0, $limit = 10)
    {
        $query = $this->makeModel()->newQuery()->select(
                                                    'id',
                                                    'title',
                                                    'content',
                                                    'video_id',
                                                    'lesson_id',
                                                    'origin_url',
                                                    'short_url',
                                                    'options',
                                                    'status',
                                                    'type',
                                                    'created_at',
                                                    'is_sent_all',
                                                    'send_time',
                                                    'created_at',
                                                    'updated_at'
                                                )->where('status', 0);
        $result = $query->offset($offset)
                        ->limit($limit)
                        ->orderBy('updated_at', 'desc')
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
    public function updateStatusById($id)
    {
        try{
            $updata = [
                'status'     => 1,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];
            return $this->makeModel()->newQuery()->where('id', $id)->update($updata);
        }catch(\Exception $ex) {
            Log::info('r=messageApiReposiroty f=updateStatusById msg='.$ex->getMessages());
            return false;
        }
    }

}
