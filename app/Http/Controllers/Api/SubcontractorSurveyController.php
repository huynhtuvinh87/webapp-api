<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\Notifications\QueueSurvey;
use App\Jobs\Notifications\SendSurveyConfirmation;
use App\Models\SubcontractorSurvey;
use App\Notifications\Internal\NewLead;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SubcontractorSurveyController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        try {

            $request_data = $request->validate([
                'answer' => 'required|string|min:2',
            ]);

            SubcontractorSurvey::updateOrCreate(
                ['role_id' => $request->user()->role->id, 'entity_key' => $request->user()->role->entity_key, 'entity_id' => $request->user()->role->entity_id],
                ['answer' => $request_data['answer']]
            );

//            QueueSurvey::dispatch();

            // Send user a thank you email if answer is YES
            if($request_data['answer'] == 'yes'){
                SendSurveyConfirmation::dispatch($request->user()->role->id);
            }

            // Send internal notification, if answer is YES or MAYBE
            if ($request_data['answer'] != 'no') {
                Notification::route('mail', config('api.subcontractor_survey.lead_email'))
                    ->notify(new NewLead($request->user()->role->id, $request_data['answer']));
            }

            return response(['message' => 'Thanks for the answer'], 202);

        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
