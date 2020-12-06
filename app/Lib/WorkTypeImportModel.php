<?php

namespace App\Lib;

use App\Models\WorkType;
use Maatwebsite\Excel\Concerns\ToModel;

class WorkTypeImportModel implements ToModel
{
    protected $work_types = [];

    public function __construct()
    {
        $this->work_types = WorkType::all();
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $code = $row[0];
        $name = $row[1];
        if (!is_string($code)) {
            //
            return new WorkType([
                'parent_id' => $this->getParentID($code),
                'code' => $code,
                'name' => $name
            ]);
        }
    }

    /**
     * Get parent ID.
     *
     * @param str $code
     * @return mixed
     */
    public function getParentID(string $code)
    {
        $this->work_types = WorkType::all();
        $code = (string) $code;

        foreach($this->work_types as $work_type) {
            $work_type_code = (string) $work_type->code;
            //
            if (starts_with($code, $work_type_code)) {
                //
                // dump($code . ' >> ' . $work_type_code);
                // dump(strlen($code) . ' & ' . strlen($work_type_code));
                // dump('========================');

                if (strlen($code) - strlen($work_type_code) == 1) {
                    return $work_type->id;
                }
            }
        }

        return null;
    }
}
