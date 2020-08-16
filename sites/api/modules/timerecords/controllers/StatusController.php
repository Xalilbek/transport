<?php

namespace Controllers;

use Custom\Models\TimeRecords;
use Custom\Models\Cache;
use Lib\Auth;
use Lib\Req;
use Lib\TimeZones;

class StatusController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $error = false;
        $req = (array)Req::get();

        $employee_id = (string)Auth::getData()->_id;
        $data = TimeRecords::findFirst([
            [
                "employee_id" => (string)$employee_id,
                "start_date" => ['$ne' => null],
                "end_date" => null,
                "is_deleted" => [
                    '$ne' => 1
                ]
            ],
            "sort" => [
                "_id" => -1,
            ],
        ]);

        $response = array(
            "status" => "success",
            "description" => false,
            "data" => $data ? [
                "id" => (string)$data->_id,
                "start_date" => TimeZones::date($data->start_date, "Y-m-d H:i:s"),
                "start_unixtime" => TimeRecords::toSeconds($data->start_date)
            ] : false
        );

        if ($error) {
            $response = [
                "status" => "error",
                "error_code" => 1017,
                "description" => $error,
            ];
        }
        echo json_encode((object)$response);
        exit;
    }
}
