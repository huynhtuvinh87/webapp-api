<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    protected $fillable = [

    ];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function getFileAttribute($value)
    {

        if (!$value) {
            return null;
        }

        if (config('filesystems.default') === "s3") {
            return Storage::temporaryUrl($value, now()
                ->addMinutes(config('filetypes.private_link_life.long')));
        }

        return Storage::url($value);
    }
}
