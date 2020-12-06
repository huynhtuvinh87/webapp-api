<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InviteController extends Controller
{

    public function index(Request $request) {

        $invite_link =  config('app.webapp').'/#/contractor-signup/?token=';

        $invites = DB::table('contractor_hiring_organization')
            ->join('contractors', 'contractors.id', '=', 'contractor_hiring_organization.contractor_id')
            ->join('hiring_organizations', 'hiring_organizations.id', '=', 'contractor_hiring_organization.hiring_organization_id')
            ->join('roles', function($query){
                $query->on('roles.entity_id', '=', 'contractors.id')
                    ->where('roles.entity_key', 'contractor')
                    ->where('roles.role', 'owner');
            })
            ->join('users', 'users.id', '=', 'roles.user_id')
            ->where('contractor_hiring_organization.accepted', '0')
            ->whereNotNull('contractor_hiring_organization.invite_code')
            ->where(function($query) use ($request){

                if ($request->has('search') && $request->query('search') !== ''){

                    $query->where('contractors.name', 'like', '%'. $request->query('search').'%')
                        ->orWhere('hiring_organizations.name', 'like', '%'. $request->query('search').'%');

                }

                return;

            })
            ->selectRaw("contractors.name as contractor, users.email as contractor_email, hiring_organizations.name as organization, CONCAT('$invite_link', contractor_hiring_organization.invite_code) as invite_code")
            ->paginate(20);

        return response([
            'pending_invites' => $invites
        ]);
    }

    public function show(){
        return view('admin.invites');
    }

}
