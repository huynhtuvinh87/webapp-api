<?php

namespace App\Models;

use App\Models\File;
use App\Models\Requirement;
use Illuminate\Database\Eloquent\Model;

class RequirementContent extends Model
{

    protected $appends = [
        'filePath',
    ];

    protected $fillable = [
        'name',
        'description',
        'lang',
        'text',
        'url',
        'requirement_id',
        'file_id',
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function getFilePathAttribute()
    {
        if (isset($this->file)) {
            return $this->file->getFullPath();
        } else {
            return null;
        }

    }
}
