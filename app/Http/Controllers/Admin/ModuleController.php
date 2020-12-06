<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Traits\ModuleTrait;
use Illuminate\Http\Request;
use Log;

class ModuleController extends Controller
{
    use ModuleTrait;

    public function index(Request $request)
    {

        if ($request->wantsJson()) {
            return response()->json(["modules" => Module::where(function ($query) use ($request) {

                //Only execute if search is specified
                if (!$request->has('search')) {
                    return;
                }

                $query->where('name', 'LIKE', "%" . $request->get('search') . "%");

            })->paginate(20)]);
        }

        return view('admin.modules.index');

    }

    public function show(Request $request, $id)
    {
        $module = Module::find($id);
        $responseObject = array_merge(
            ['module' => $module],
        );

        if (\Illuminate\Support\Facades\Request::wantsjson()) {
            return response()->json($responseObject);
        }
        return view('admin.modules.show', $responseObject);
    }

    public function visibilities(Request $request, $id)
    {
        $module = Module::find($id);
        return response()->json($this->allModuleVisibilities($module));
    }
}
