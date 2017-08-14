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
class Message extends Model 
{

    protected $table = 'messages';
    protected $fillable = [
        'title',
        'content',
        'origin_url',
        'short_url',
        'options',
        'send_count',
        'admin_id',
        'type',
        'status',
        'is_push_type',
        'is_sent_all',
        'send_time',
        'lesson_id',
        'video_id',
        'created_at',
        'updated_at'
    ];
//    public $timestamps = true;
}