<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'notification', // Notification Class used
        'data' // JSON data used for notification
    ];

    public function notifiable() : MorphTo
    {
        return $this->morphTo();
    }
}
