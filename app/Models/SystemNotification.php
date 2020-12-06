<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemNotification extends Model
{

    use SoftDeletes;

    public $dates = [
        'deleted_at',
        'read_at'
    ];

    protected $fillable = [
        'message',
        'action_text',
        'action',
        'user_id',
        'sender_id'
    ];

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo User
     */
    public function user(){
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|null
     */
    public function sender(){
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Read message
     */
    public function read(){

        if ($this->attributes['read_at'] === null){
            $this->read_at = Carbon::now();
            $this->save();
        }

    }
}
