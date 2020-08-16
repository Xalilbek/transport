<?php

namespace Controllers;

use Custom\Models\TimeRecords;
use Custom\Models\Cache;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = (array)Req::get();
        $id = (string)$req['id'];
        $employee_id = (string)trim($req['employee_id']);
        $start_date = (string)trim($req['start_date']);
        $end_date = (string)trim($req['end_date']);
        $category_id = (string)trim($req['category_id']);

        $data = TimeRecords::findFirst([
            [
                "_id" => TimeRecords::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (Cache::is_brute_force("timerecordsEdit-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['timerecords_update']['allow']) {
                $update = [
                    "employee_id" => (string)$employee_id,
                    //"start_date"  => $start_date ? TimeRecords::getDate(strtotime($start_date)) : null,
                    //"end_date"    => $end_date ? TimeRecords::getDate(strtotime($end_date)) : null,
                    "start_date" => $start_date ? TimeZones::date(strtotime($start_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]) : null,
                    "end_date" => $end_date ? TimeZones::date(strtotime($end_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]) : null,
                    "category_id" => (string)$category_id,
                    "updated_at" => TimeRecords::getDate(),
                ];

                TimeRecords::update(["_id" => $data->_id], $update);
                $response = [
                    "status" => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                ];

                // Log start
                Activities::log([
                    "user_id" => (string)Auth::getData()->_id,
                    "section" => "timerecords",
                    "operation" => "timerecords_update",
                    "values" => [
                        "id" => $data->_id,
                    ],
                    "oldObject" => $data,
                    "newObject" => TimeRecords::findById($data->_id),
                    "status" => 1,
                ]);
                // Log end
            } else {
                $error = Lang::get("PageNotAllowed");
            }
        }
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
