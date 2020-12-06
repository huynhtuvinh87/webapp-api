<?php

namespace App\Http\Controllers\Admin;

use App\Models\Contractor;
use App\Http\Controllers\Controller;
use App\Traits\CacheTrait;
use App\ViewModels\ViewContractorOverallCompliance;
use App\ViewModels\ViewEmployeeOverallCompliance;
use Illuminate\Http\Request;
use Cache;
use DB;
use Log;
use Artisan;

class ContractorController extends Controller
{
    use CacheTrait;

    public function index(Request $request){

        if ($request->wantsJson()){
            return response()->json(["contractors" => DB::table('contractors')
                ->where(function($query) use ($request){

                    if (!$request->has('search')){
                        return;
                    }

                    $query->where('contractors.name', 'LIKE', "%".$request->get('search')."%");

                })
                ->select([
                    'contractors.id as id',
                    'contractors.name as name',
                    'contractors.is_active',
                    'contractors.created_at as created_at',
                    'subscriptions.ends_at as ends_at',
                ])
                ->leftJoin('subscriptions', function($query){
                    $query->on('contractors.id', '=', 'subscriptions.contractor_id');
                })
                ->paginate(20)
            ]);
        }

        return view('admin.contractor.index');

    }

    public function show(Request $request, $id){

        //Reference UserController for explanation
        if (\Illuminate\Support\Facades\Request::wantsjson()) {
            $users = DB::table('users')
                ->leftJoin('roles', 'users.id', '=', 'roles.user_id')
                ->leftJoin('contractors', function($query) use ($id){
                    $query->on('contractors.id', '=', 'roles.entity_key')
                        ->where('contractors.id', '=', $id);

                })
                ->leftJoin('subscriptions', 'contractors.id', '=', 'subscriptions.contractor_id')
                ->where(function($query) use ($request){

                    if (!$request->has('search')){
                        return;
                    }

                    $query->where('users.email', 'LIKE', '%'.$request->input('search').'%')
                        ->orWhere('contractors.name', 'LIKE', '%'.$request->input('search').'%');
                })
                ->select(
                    "users.id as user_id",
                    "users.email",
                    "roles.role",
                    "roles.entity_key",
                    "contractors.name as contractor",
                    "subscriptions.ends_at as sub_expiring"
                )
                ->where('roles.entity_key', 'contractor')
                ->where('roles.entity_id', $id)
                ->orderBy('users.id')
                ->paginate($request->input('rowsPerPage', 10));

            return response()->json(["users" => $users]);
        }

        $contractor = Contractor::find($id);


        return view('admin.contractor.show', [
            "contractor" => $contractor
        ]);
    }

    public function compliance(Request $request, $id){

        $contractor = ViewContractorOverallCompliance::where('contractor_id', $id)->first();

        $employees = ViewEmployeeOverallCompliance
            ::where('entity_key', 'contractor')
            ->where('entity_id', $id)->get();

        $compliance = 0;

        if ($employees->count() !== 0){
            $compliance = round($employees->sum('compliance') / $employees->count(), 0);
        }

        return response([
            'contractor' => $contractor->compliance,
            'employee' => $compliance
        ]);

    }

    /**
     * Resends the stripe metadata information for the given contractor ID
     *
     * @param Request $request
     * @param [type] $id contractor_id
     * @return void
     */
    public function updateStripe(Request $request, $id){
        Artisan::queue('stripe:send-metadata', [
            '--contractor' => $id
        ]);


        return response(['message' => "Stripe Metadata job queued"],200);
    }

    /**
     * Enable/Disables HO Status
     * @param Contractor $contractor
     * @return null
     */
    public function toggleIsActive(Contractor $contractor){

        $contractor->is_active = !$contractor->is_active;
        $contractor->save();

        Cache::tags([$this->getContractorCacheTag($contractor)])->flush();

        return response(['message' => "Contractor enabled / disabled"], 200);
    }


    /**
     * Takes in an ID and clears the elements cache
     *
     * @param $id
     * @return void
     */
    public function clearCache($id)
    {
        $obj = Contractor::find($id);
        $className = $this->getClassName($obj);
        $success = false;
        $message = null;

        try {
            Cache::tags($this->buildTagFromObject($obj))->flush();
            $success = true;
            $message = $obj->name . " cache was cleared successfully";
        } catch (Exception $e) {
            Log::warn("Failed to clear cache for '$id'.", [
                $className => $obj,
                'Exception' => $e
            ]);
            $success = false;
            $message = "Failed to clear cache";
        }
            // return redirect('/admin/users/' . $id);
            return response()->view('admin.contractor.index', ['contractor' => $obj, "success" => $success, "message" => $message]);
    }
}
