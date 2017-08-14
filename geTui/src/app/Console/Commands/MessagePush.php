<?php

namespace  GeTui\App\Console\Commands;

use Illuminate\Console\Command;
use GeTui\App\Repositories\MessageApiRepository;
use Illuminate\Support\Facades\Log;

class MessagePush extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'msg:push';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(MessageApiRepository $messageApiRepository)
    {
        parent::__construct();
        $this->messageApiRepository = $messageApiRepository;
        // $this->userAppApiRepository = $userAppApiRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $limit  = 100;
        $offset = 0;
        $count  = 0;
        do{
            $results  = $this->messageApiRepository->getList([], $offset, $limit);
            $count   += count($results);

            if(!$results) {
                Log::info('job=messagePush msg=发送push完成 count='.$count);
                break;
            }
            foreach($results as $key => $result) {
                if(isset($result['send_time']) && (time()<strtotime($result['send_time']))) {
                    Log::info('job=messagePush msg=未到发送时间 results='.json_encode($result));
                    continue;
                }
                $flag = app('MessageToApp')->updateMessageStatus($result['id'], $result['is_sent_all']);
                Log::info('job=messagePush msg=发送push updateMessageFlag='.$flag.'results='.json_encode($result));
                if($flag) {
                    app('MessageToApp')->sendPush($result, config('getui.push_flag'));
                }
            }
            $offset += $limit;
        }while($results);
    }
}
