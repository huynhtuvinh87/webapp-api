<?php

namespace App\Jobs;

use App\Models\Contractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class UpdateStripeInfo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $contractor_id;

    /**
     * Create a new job instance.
     *
     * @param $contractor_id
     */
    public function __construct($contractor_id)
    {
        $this->queue = 'low';
        $this->connection = 'database';

        $this->contractor_id = $contractor_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug("Job to update Stripe for $this->contractor_id");
        $contractor = Contractor::find($this->contractor_id);
        if ($contractor instanceof Model) {
            $contractor->updateStripeInfo();
        }
    }
}
