<?php

namespace App\Lib\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Include this route in controllers that will export reports.
 *
 * Trait ReportControllerTrait
 * @package App\Lib\Traits
 */
trait ReportControllerTrait
{

    /**
     *
     *
     *
     *
     *
     * @param $request
     * @param $data DB|array select result
     * @param $headers [ ["text" => "Header 1, "value" => "header_1"],["text" => "Header 2", "value" => "header_2] ]
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    protected function report($request, $data, $headers){

        if ($request->has('download')){

            return response()->stream(function() use ($headers, $data) {

                $file = fopen('php://output', 'w');

                $headers_arr = [];

                foreach($headers as $header){
                    array_push($headers_arr, $header['text']);
                }

                fputcsv($file, $headers_arr);

                foreach ($data as $row){

                    $row = json_decode(json_encode($row), true); //convert Annoymous Class to array

                    fputcsv($file, $row);
                }

                fclose($file);

            }, 200, [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=file.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ]);

        }

        return response(["headers" => $headers, "data" => $data]);

    }

}
