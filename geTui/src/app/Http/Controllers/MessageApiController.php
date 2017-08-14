<?php

namespace  GeTui\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GeTui\App\Repositories\MessagePushApiRepository;
use Illuminate\Support\Facades\Auth;

require_once  base_path(). '/packages/geTui/geTui/getui/IGt.Push.php';

class MessageApiController
{
	public function __construct(MessagePushApiRepository $messagePushApiRepository)
    {
        $this->messagePushApiRepository = $messagePushApiRepository;
    }

    public function index(Request $request)
    {
        $options = $request->all();
        Log::info('c=messageApi f=index options='.json_encode($options));
        $user = Auth::guard('api')->user();
        $uid  = 0;
        if($user) {
            $uid = $user->id;
        }
        $where   = ['is_sent_all' => 1];
        if($uid != 0) {
            $where['uid'] = $uid;
        }
        Log::info('c=message f=list  options='.json_encode($options));
        $page    = isset($options['page']) ? (int)$options['page'] : '1';
        $size    = isset($options['size'])  ? (int)$request->get('size') : config('page.default_size');
        $offset = ($page-1)*$size;
        $lists  = $this->messagePushApiRepository->getList($where, $offset, $size);
        $output = [
                'error' => 0,
                'msg'   => 'ok',
                'data'  => ['page' => $page, 'list' => $lists]
            ];
        return response($output);
    }
}
