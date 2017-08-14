<?php
namespace GeTui\App\Entities;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use App\Traits\SequenceTrait;

/**
 * App\Entities\UserUnion
 *
 * @mixin \Eloquent
 */
class UserApp extends Model
{
    use SequenceTrait;
    protected $table = 'worker_apps';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'worker_id',
        'device_id',
        'device_os',
        'is_logout',
        'created_at',
        'updated_at'
    ];
}
