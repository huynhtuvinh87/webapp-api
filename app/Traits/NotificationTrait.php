<?php

namespace App\Traits;

use App\Models\Contractor;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Log;
use Exception;

trait NotificationTrait
{
    /**
     * isUserEmailValid
     * Andrew Lampert.
     *
     * Takes in a user and checks their email
     * If the email is not valid, marks the email as invalid in the database
     *
     * @param mixed $user
     * @return mixed
     * @throws Exception
     */
    protected function isUserEmailValid($user)
    {
        // Error handling
        if (!isset($user)) {
            throw new Exception("User was not defined, can't validate email");
        }
        if (!isset($user->email)) {
            throw new Exception("User's email is not defined.");
        }

        // If email is already marked as invalid, return status
        if (!$user->verified_email) {
            return $user->verified_email;
        }

        // Email validation
        if (is_null($user->email_verified_at)) {
            return false;
        }

        // if employee, verify if email is validated
        if(isset($user->role->role) && $user->role->role == 'employee'){
            if($user->email_verified_at <= config('api.email_validation_date')){
                return false;
            }
        }

        // Checking format of email
        $validEmail = filter_var($user->email, FILTER_VALIDATE_EMAIL);

        // If email is invalid, mark it as invalid in the database
        if (!$validEmail) {
            Log::info('Invalid email for ' . $user->email);
            User::where('id', $user->id)
            ->update([
                'verified_email' => false,
                'email_verified_at' => null
            ]);
        }

        return $validEmail;
    }

    protected function logNotification($notifiable, $data)
    {
    	$data = [
            'notifiable_type' => get_class($notifiable),
            'notifiable_id' => $notifiable->id ?? 0,
            'notification' => get_class($this),
            'data' => json_encode($data)
        ];

        $notification = NotificationLog::create($data);
        //Log::info('Sending notification to ' . get_class($notifiable) . ' : ' . $notifiable->id);
    }

    /**
     * filterOutInactiveContractors.
     *
     * Applies a filter to a collection
     * Removes contractors with no subscription information
     *
     * @param Collection $contractors
     * @return Collection
     */
    protected function filterOutInactiveContractors(Collection $contractors) : Collection
    {
        return $contractors->filter(function ($contractor) {
            return $this->checkContractorSubscriptionStatus($contractor);
        });
    }

    /**
     * filterOutContractors by Model Instance
     *
     * @param Contractor $contractor
     * @return mixed
     */
    public function checkContractorSubscriptionStatus (Contractor $contractor)
    {
        $contractorHasSubscription = $contractor->subscribed();
        return $contractorHasSubscription;
    }
}
