<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MoveFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $file = null;
    private $args = [];
    private $deleteOriginal = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        File $file,
        $args,
        $deleteOriginal = false
    ) {
		$this->queue = 'default';
        $this->connection = 'database';

        $this->file = $file;
        $this->args = $args;
        $this->deleteOriginal = $deleteOriginal;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $this->file->move($this->args, $this->deleteOriginal);
        } catch(Exception $e){
            Log::error($e->getMessage());
        }
    }
}
