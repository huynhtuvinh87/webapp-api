<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function index()
    {
        return response()->view('admin.exports.exports');
    }

    //TODO CACHE this monstrous query when unpaginated (Possibly even paginate after retrieving from this cache)
    public function users(Request $request)
    {

        //TODO generate column selection based on query columns, add to where clause, add to CSV headers

        //get users
        $users = DB::table('users')
            ->leftJoin('roles', 'users.id', '=', 'roles.user_id')
            ->leftJoin('hiring_organizations', 'hiring_organizations.id', '=', 'roles.entity_key')
            ->leftJoin('contractors', 'contractors.id', '=', 'roles.entity_key')
            ->leftJoin('subscriptions', 'contractors.id', '=', 'subscriptions.contractor_id')
            ->select(
                "users.id",
                "users.email",
                "roles.role",
                "roles.entity_key",
                "hiring_organizations.name as hiring_org",
                "contractors.name as contractor",
                "subscriptions.ends_at as sub_expiring"
            );

        //Filters
        switch ($request->query('filter')) {

            case "contractors":
                $users->where('roles.entity_key', "contractor");
                break;

            case "hiring_organizations":
                $users->where('roles.entity_key', "hiring_organization");
                break;

            case "subscribed":
                $users->whereNotNull('subscriptions.ends_at');
                break;

        }

        $users = $users->orderBy('users.id')->get();

        $csv = \League\Csv\Writer::createFromFileObject(new \SplTempFileObject());

        $csv->insertOne(
            [
                "user_id",
                "users.email",
                "roles.role",
                "roles.entity_key",
                "hiring_org",
                "contractor",
                "sub_expiring"
            ]
        );

        $length = count($users);

        for ($i = 0; $i < $length; $i++){
            $csv->insertOne((array) $users[$i]);
        }

        $csv->output('users.csv');

        return response()->json($users);


    }
}
