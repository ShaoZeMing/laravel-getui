<?php

namespace GeTui\App\Repositories;

use DB;
use Validator;
use GeTui\App\Entities\UserApp;
// use GeTui\App\Validators\UserAppValidator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\UserApiRepository as UserRepository;
use Illuminate\Support\Facades\Log;
use Prettus\Repository\Criteria\RequestCriteria as RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class UserAppApiRepository extends BaseRepository
{

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserApp::class;
    }

    /**
    //  * Specify Validator class name
    //  *
    //  * @return mixed
    //  */
    // public function validator()
    // {
    //     return UserAppValidator::class;
    // }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function getUserAppByDevId($devId, $uid=0)
    {
        $query = $this->makeModel()->newQuery()->where('device_id', $devId);
        if($uid) {
            $query = $query->where('worker_id', $uid);
        }
        $data = $query->first();
        return $data;
    }

    public function getUserAppByWhere($where, $offset=0, $limit=1)
    {
        $data = $this->makeModel()->newQuery()
                                    ->where($where)
                                    ->offset($offset)
                                    ->limit($limit)
                                    ->get()
                                    ->toArray();
        return $data;
    }

    public function insertOrUpdateData($data, $type='register')
    {
        $appData = [
            'worker_id'        => isset($data['worker_id']) ? (int)$data['worker_id'] : 0,
            'device_id'  => $data['device_id'],
            'device_os'  => isset($data['device_os']) ? $data['device_os'] : "",
            'created_at' => date('Y-m-d H:i:s', time())
        ];
        if($type == 'register') {
            $appData['created_at'] = date('Y-m-d H:i:s', time());
        } else {
            $appData['updated_at'] = date('Y-m-d H:i:s', time());
        }
        $where = ['device_id' => $data['device_id'], 'worker_id' => 0];
        return $this->makeModel()->newQuery()->updateOrCreate($where, $appData);
    }

    public function insert($data)
    {
        $appData = [
            'worker_id'  => isset($data['worker_id']) ? (int)$data['worker_id'] : 0,
            'device_id'  => $data['device_id'],
            'device_os'  => isset($data['device_os']) ? $data['device_os'] : "",
            'is_logout'  => isset($data['is_logout']) ? $data['is_logout'] : 0,
            'created_at' => date('Y-m-d H:i:s', time())
        ];
        $appData = $this->makeModel()->fill($appData)->toArray();
        return parent::create($appData);
    }

    public function keepOnlyOneOnline($devId, $devOs, $uid)
    {
        if(!$this->getUserAppByDevId($devId, $uid)) {
            $data = [
                'worker_id' => $uid,
                'device_id' => $devId,
                'device_os' => $devOs,
                'is_logout' => 0
                ];
            $this->insert($data);
        } else {
            $onlineData = [
                'is_logout' => 0,
            ];
            $this->makeModel()->select('id', 'dev_id','is_logout')
                              ->where('worker_id', $uid)
                              ->where('device_id', $devId)
                              ->update($onlineData);
        }

        $delineData = [
            'is_logout' => 1,
        ];
        $this->makeModel()->select('id', 'dev_id','is_logout')
                          ->where('worker_id', $uid)
                          ->where('device_id', '!=', $devId)
                          ->update($delineData);
    }

    public function getUserAppByUid($uid, $offset=0, $limit=1)
    {
        $data = $this->makeModel()->newQuery()
                                ->whereIn('worker_id', $uid)
                                ->offset($offset)
                                ->limit($limit)
                                ->get()
                                ->toArray();
        return $data;
    }
}
