<?php

namespace GeTui\App\Http\Controllers;

use App\Http\Controllers\Controller;
use GeTui\App\Repositories\UserPushOptionRepository;
use GeTui\App\Validators\MessageValidator;
use Illuminate\Foundation\Validation\ValidatesRequests;

use GeTui\App\Repositories\MessageRepository;
use GeTui\App\Repositories\MessagePushRepository;
use Illuminate\Http\Request;
use Prettus\Validator\Contracts\ValidatorInterface;


class MessageController extends Controller
{
    protected $message;    //模型 VideoRepository
    protected $request;  //请求数据
    protected $userPushOption;  //请求数据
    protected $messagePush;  //请求数据

    public function __construct(Request $request, MessageRepository $messageRepository,
                                UserPushOptionRepository $userPushOption,
                                MessagePushRepository $messagePushRepository)
    {
        $this->request = $request;
        $this->message = $messageRepository;
        $this->userPushOption = $userPushOption;
        $this->messagePush = $messagePushRepository;

    }

    /**
     * push消息列表
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */

    public function index()
    {
        $data['data'] = $this->message->selectData();
        //处理序号
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $data['number'] = 1 + (config('page.admin_page', 10) * ($page - 1));
        return view('geTui::admin.message.message_index', $data);
    }


    /**
     * push消息数据筛选
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */

    public function search()
    {
        $request = $this->request->all();
        if ($request) {
            $request = arrayFilterEmpty($request); //过滤请求数组数据中值为空和为all的数据
            $data = $this->userPushOption->searchUserPushOption($request); //利用条件查找数据并分页
            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $data['number'] = 1 + (config('page.admin_page', 10) * ($page - 1));
            $data['request'] = $request;
            return view('geTui::admin.message.message_search', $data);
        } else {
            return view('geTui::admin.message.message_search');

        }
    }


    /**
     * push消息添加页
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function pushCreate()
    {
        return view('geTui::admin.message.push_add');
    }


    /**
     * push消息添加页
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function newsCreate()
    {
        return view('geTui::admin.message.appnews_add');
    }

    /**
     * push消息状态修改
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function saveStatus()
    {

        $id = $this->request->id;
        //撤回发送，修改数据
        $find = $this->message->find($id);
        $find->status = 2;
        $result = $find->save();
        if ($result) {
            return [
                'error' => false,
                'message' => '撤回成功',
            ];
        } else {
            return [
                'error' => true,
                'message' => '撤回失败',
            ];

        }
    }


    /**
     * push消息添加
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function pushAdd(MessageValidator $validator)
    {
        //验证数据合法性
        $this->validate($this->request,
            $validator->getRules(ValidatorInterface::RULE_CREATE),
            $validator->errorsBag()
        );

        $data = $this->request->all();
        $result = $this->message->insertData($data, $this->userPushOption);
        if ($result) {
            return [
                'error' => false,
                'message' => '保存成功',
            ];
        } else {
            return [
                'error' => true,
                'message' => '保存失败',
            ];
        }

    }


}