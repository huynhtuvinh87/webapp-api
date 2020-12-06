<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Lib\Traits\UniquifyTrait;
use Exception;

class WorkType extends Model
{
    use UniquifyTrait;

    protected $fillable = [
        'parent_id',
        'code',
        'name'
    ];

    public function children(){
        return $this->hasMany(WorkType::class, 'parent_id');
    }

    public function getChildren(){
        $children = WorkType::where('parent_id', $this->id);
        return $children;
    }

    /**
     * For sorting
     *
     * @param WorkType $object1
     * @param WorkType $object2
     */
    protected static  function comparator($object1, $object2) {
        return $object1->id > $object2->id;
    }

    /**
     * Get an array of all contractors for the current work type
     *
     * @return void
     */
    public function contractors(){
        // Array of unique contractors to be returned
        $contractors = [];

        // Getting contractors - work type mapping
        $contractorsInWorkTypeMap = DB::table('contractor_work_type')
            ->select([
                'id',
                'work_type_id',
                'contractor_id',
                'created_at',
                'updated_at'
            ])
            ->where('work_type_id', $this->id)
            ->get();

        // Getting contractors model from work type mapping

        foreach($contractorsInWorkTypeMap as $contractorMap){
            // Getting contractor object
            $contractorObj = Contractor::where('id', $contractorMap->contractor_id)
                ->get()
                ->first();

            array_push($contractors, $contractorObj);
        }

        return $this->uniquify($contractors, 'id');
    }

}
