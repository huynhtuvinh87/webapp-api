<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Lib\Traits\UniquifyTrait;
use App\Models\User;
use App\Models\WorkType;
use App\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Exception;

class WorkTypeController extends Controller
{
    use UniquifyTrait;

    private static $hiringOrgContractorIDs = [];

    /**
     * Get WorkTypes
     * Parent if no $id, otherwise children of provided id
     * OR if search param is included, search whole table for matches
     * @param null $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index(Request $request, $id = null)
    {
        if ($request->query('search')) {
            return response(WorkType::where('name', 'LIKE', "%" . $request->query('search') . "%")->get());
        }

        if ($request->query('all')) {
            return response(WorkType::get());
        }

        if ($id === null) {
            return response(WorkType::whereNull('parent_id')->get());
        }

        return response(WorkType::where('parent_id', $id)->withCount('children')->get());
    }

    /**
     * All company's work types
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    function list(Request $request) {
        return response($request->user()->role->company->workTypes);
    }

    /**
     * Get all work types
     *
     * @param Request $request
     * @return void
     */
    function listAll(Request $request){
        return response(WorkType::get());
    }

    /**
     * Create a new worktype for the company
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            "work_type_id" => "required|numeric",
        ]);

        if ($request->user()->role->role !== 'owner' && $request->user()->role->role !== 'admin') {
            return response('not authorized', 403);
        }

        if ($request->user()->role->entity_key === 'contractor') {
            DB::table('contractor_work_type')
                ->insert([
                    "contractor_id" => $request->user()->role->entity_id,
                    "work_type_id" => $request->get('work_type_id'),
                    "created_at" => now(),
                ]);
        } elseif ($request->user()->role->entity_key === "hiring_organization") {
            DB::table('hiring_organization_work_type')
                ->insert([
                    "hiring_organization_id" => $request->user()->role->entity_id,
                    "work_type_id" => $request->get('work_type_id'),
                    "created_at" => now(),
                ]);
        }

        return response("Ok");
    }

    /**
     * Disassociate worktype for company
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function delete(Request $request, $id)
    {
        if ($request->user()->role->entity_key === "contractor") {
            DB::table('contractor_work_type')
                ->where('contractor_id', $request->user()->role->entity_id)
                ->where('work_type_id', $id)
                ->delete();
        } else {
            DB::table('hiring_organization_work_type')
                ->where('hiring_organization_id', $request->user()->role->entity_id)
                ->where('work_type_id', $id)
                ->delete();
        }

        return response("ok");
    }

    /**
     * Getting all contractors assigned to ncis code
     *
     * @param Request $request
     * @param WorkType $workType
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws Exception
     */
    public function contractorsByCode(Request $request, WorkType $workType)
    {
        $contractors = [];
        $workTypeCodes = [];
        // Getting NCIS code from work type
        $ncisCode = $workType->code;

        if (!isset($ncisCode)) {
            throw new Exception("NCIS code was null!");
        }

        // Get All relevant codes by ncis code
        $workTypes = $this->getChildWorkTypesByNCISCode($ncisCode, 0);

        foreach($workTypes as $workType){
            array_push($workTypeCodes, $workType->id);
        }

        $contractorQuery = Contractor::join('contractor_work_type', 'contractors.id', '=', 'contractor_work_type.contractor_id')
            ->select('contractors.id', 'contractors.name', 'contractors.state')
            ->leftJoin('contractor_hiring_organization', function($query) use ($request){
                $query
                    ->on('contractor_hiring_organization.contractor_id', '=', 'contractors.id')
                    ->where('contractor_hiring_organization.hiring_organization_id', '=', $request->user()->role->entity_id);
            })
            ->whereIn('contractor_work_type.work_type_id', $workTypeCodes)
            ->groupBy('contractors.name', 'contractors.state', 'contractors.id');

        $contractorByQuery = $contractorQuery->get();

        // Returning data
        return response([
            'contractors' => $contractorByQuery,
        ]);
    }

    /**
     * Grab an array of all the work types by ncis code
     *
     * @param Int $ncisCode
     * @return array
     */
    private function getChildWorkTypesByNCISCode(Int $ncisCode, Int $level)
    {
        // Array of the work type by the root code, and all the children
        // To be returned recursively in a 1D array
        $workTypes = [];

        // Get contractors for current NCIS code
        $workTypesByRootCode = WorkType::where('code', $ncisCode)->get()->first();

        // Booleans
        $isOverLevelLimit = $level > 10;
        $codeHasChildren = isset($workTypesByRootCode->children) && sizeof($workTypesByRootCode->children) > 0;

        // Error Handling - Making sure that the level doesn't exceed a ridiculous level
        if ($isOverLevelLimit) {
            return $workTypesByRootCode;
        }

        // If child NCIS codes exist, recurse
        if ($codeHasChildren) {

            // Getting children work types
            $workTypeChildren = $workTypesByRootCode->children;

            // For each child, get child codes and add it to workTypes array
            foreach ($workTypeChildren as $workTypeChild) {
                $workTypeChildrenChildren = $this->getChildWorkTypesByNCISCode($workTypeChild->code, $level + 1);

                foreach ($workTypeChildrenChildren as $childChild) {
                    $isChildChildSet = isset($childChild) && isset($childChild->id);
                    if ($isChildChildSet) {
                        array_push($workTypes, $childChild);
                    }
                }
            }
        }

        // Pushing self to workTypes array
        unset($workTypesByRootCode->children);
        $workTypesByRootCode->depth = $level;
        array_push($workTypes, $workTypesByRootCode);

        // Filtering out duplicates
        // array_unique($workTypes);
        $this->uniquify($workTypes, 'id');

        // Sorting array
        usort($workTypes, array("App\Models\WorkType", 'comparator'));

        // Returning work types array
        return $workTypes;
    }

    protected function getContractorsByWorkType(WorkType $workType)
    {
        $contractors = $workType->contractors();

        return $contractors;
    }
}
