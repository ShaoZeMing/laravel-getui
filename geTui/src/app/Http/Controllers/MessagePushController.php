<?php

namespace GeTui\App\Http\Controllers;

use App\Http\Controllers\Controller;
use GeTui\App\Repositories\UserPushOptionRepository;
use GeTui\App\Validators\MessagePushValidator;
use GeTui\App\Validators\MessageValidator;
use Illuminate\Foundation\Validation\ValidatesRequests;

use GeTui\App\Repositories\MessageRepository;
use GeTui\App\Repositories\MessagePushRepository;
use Illuminate\Http\Request;
use Prettus\Validator\Contracts\ValidatorInterface;


class MessagePushController extends Controller
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
        $data['data'] = $this->message->selectData(1);
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $data['number'] = 1 + (config('page.admin_page', 10) * ($page - 1));
        return view('geTui::admin.messagePush.messagePush_index',$data);
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
            $data = $this->userPushOption->newsSearchUserPushOption($request); //利用条件查找数据并分页
            $page = isset($_GET['page']) ? $_GET['page'] : 1;
            $data['number'] = 1 + (config('page.admin_page', 10) * ($page - 1));
            $data['request'] = $request;
            return view('geTui::admin.messagePush.messagePush_search', $data);
        } else {
            return view('geTui::admin.messagePush.messagePush_search');

        }
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
        return view('geTui::admin.messagePush.news_add');
    }



    /**
     * 消息添加
     *
     * @author shaozeming@xiaobaiyoupin.com
     *
     * @return mixed
     */
    public function newsAdd(MessagePushValidator $validator)
    {
        //验证数据合法性
        $this->validate($this->request,
            $validator->getRules(ValidatorInterface::RULE_CREATE),
            $validator->errorsBag()
        );

        $data = $this->request->all();
        $result = $this->messagePush->insertData($data, $this->userPushOption, $this->message);
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