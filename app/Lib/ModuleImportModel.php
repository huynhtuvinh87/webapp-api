<?php

namespace App\Lib;

use App\Models\Module;
use Maatwebsite\Excel\Concerns\ToModel;

class ModuleImportModel implements ToModel
{
    protected $work_types = [];

    public function __construct()
    {
        $this->work_types = Module::all();
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $name = $row[0];
        $visible = $row[1];
        if (!is_string($visible)) {
            //
            return new Module([
                'name' => $name,
                'visible' => $visible,
            ]);
        }
    }
}
