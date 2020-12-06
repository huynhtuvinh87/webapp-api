<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\DynamicFormSubmission;
use App\Models\HiringOrganization;
use App\Models\Contractor;
use App\Models\Role;

class Rating extends Model
{
    protected $fillable = [
        'rating',
        'comments',
        'contractor_id',
        'hiring_organization_id',
		'role_id',
		'rating_system',
		'dynamic_form_submission_id'
    ];

    public function hiringOrganization() : BelongsTo
    {
        return $this->belongsTo(HiringOrganization::class);
	}

	public function dynamicFormSubmission() : BelongsTo
	{
		return $this->belongsTo(DynamicFormSubmission::class);
	}

	public function contractor() : BelongsTo
	{
		return $this->belongsTo(Contractor::class);
	}

	public function role() : BelongsTo
	{
		return $this->belongsTo(Role::class);
	}

}
