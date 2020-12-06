<?php

namespace App\Console\Commands;

use App\Models\WorkType;
use function GuzzleHttp\Promise\queue;
use Illuminate\Console\Command;
use App\Lib\WorkTypeImportModel;
use Maatwebsite\Excel\Facades\Excel;

class ImportWorkTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:naics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import NAICS codes CSV';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Importing ...');
        Excel::import(new WorkTypeImportModel, storage_path('naics.csv'));
        $this->info('imported');

        $this->info('Processing make take a few minutes');

        $worktypes  = WorkType::get();

        foreach($worktypes as $worktype){
            dispatch(function() use ($worktype){

                if (!WorkType::where('parent_id', $worktype->id)->first()){
                    $worktype->has_child = 0;
                    $worktype->save();
                }

            });
        }


    }
}
