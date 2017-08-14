<?php
namespace GeTui\App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;

/**
 * App\Entities\UserUnion
 *
 * @mixin \Eloquent
 */
class UserPushOption extends Model 
{

    protected $table = 'user_push_options';

    protected $fillable = [
    	'uid',
        'mobile',
    	'gold',
    	'is_ios_app',
    	'is_android_app',
    	'registed_time',
    	'logined_time',
    	'created_at',
    	'updated_at',
    	'locate',
        'buy_count',
    	'total_amount',
        'wechat_name',
        'created_at',
        'updated_at',
    ];

    public $timestamps = false;

}