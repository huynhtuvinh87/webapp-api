<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        "short_description",
        "long_description",
        "completion_description",
        "task_type_id",
        "target_date",
        "assigned_by",
        "assigned_to",
    ];

    /**
     * Tasks attachements
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class, 'task_id');
    }

    /**
     * User who assigned tasks
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * User who is assigned the task
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

}
