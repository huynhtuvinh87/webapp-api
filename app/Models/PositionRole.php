<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionRole extends Model
{

    protected $table = "position_role";

    protected $fillable = [
        "role_id",
        "position_id"
    ];
}
