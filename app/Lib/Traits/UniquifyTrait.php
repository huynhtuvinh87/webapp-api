<?php

namespace App\Lib\Traits;

/**
 * Uniquify Trait adds a new method called `uniquify`
 */
trait UniquifyTrait{

    /**
     * `uniquify` allows you to remove duplicate entries based on a specified field
     * array_unique works for simple, 1D arrays of basic types, but doesn't really work for objects
     * Picks the first one in the array
     *
     * @param [type] $arrayOfObjects
     * @param [type] $field
     * @return void
     */
    protected function uniquify($arrayOfObjects, $field){
        $fieldsInArray = [];
        $uniqueArray = [];

        foreach($arrayOfObjects as $object){
            $fieldVal = $object[$field];
            $isFieldInArray = in_array($fieldVal, $fieldsInArray);
            if(!$isFieldInArray){
                array_push($fieldsInArray, $fieldVal);
                array_push($uniqueArray, $object);
            }
        }

        return $uniqueArray;
    }

}
