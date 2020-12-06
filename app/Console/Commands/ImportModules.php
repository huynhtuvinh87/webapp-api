<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Module;
use App\Lib\ModuleImportModel;
use Maatwebsite\Excel\Facades\Excel;

class ImportModules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:modules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import modules from csv';

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
        Excel::import(new ModuleImportModel, storage_path('modules.csv'));
        $this->info('imported');

        $this->info('Processing make take a few minutes');

        $modules  = Module::get();

        foreach($modules as $module){
            dispatch(function() use ($module){
                if (!Module::where('name', $module->name)->first()){
                    $module->save();
                }
            });
        }
    }
}
