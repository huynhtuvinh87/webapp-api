<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SystemNotificationController extends Controller
{
    public function index(Request $request){
        return response(['notifications' => $request->user()->notifications]);
    }

    public function unread(Request $request){
        return response(['notifications' => $request->user()->notifications()->whereNull('read_at')->get()]);
    }

    public function show(Request $request, SystemNotification $notification){

        if ($notification->user_id !== $request->user()->id){
            return response('not authorized', 403);
        }

        $notification->read();
        return response(['notification' => $notification]);
    }

    public function readAll(Request $request){
        $request->user()->notifications()->update([
            'read_at' => Carbon::now()
        ]);
        return response(['status' => 'ok']);
    }

    //TODO authorize proper role and user is contfactable
    public function notify(Request $request){
        $this->validate($request, [
           'user_id' => 'exists:users,id|required',
           'message' => 'string|required|max:120',
        ]);

        \App\Jobs\SystemNotification::dispatch(
            $request->get('message'),
            $request->get('user_id'),
            $request->user()->id,
            'mailto:'.$request->user()->email,
            'Reply' //TRANSLATE
        );

        return response(['status' => 'ok']);

    }
}
