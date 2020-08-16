<?php

namespace Controllers;

use Custom\Models\TimeRecords;
use Custom\Models\Cache;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;

class AddController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = (array)Req::get();

        $employee_id = (string)trim($req['employee_id']);
        $start_date = (string)trim($req['start_date']);
        $end_date = (string)trim($req['end_date']);
        $category_id = (string)trim($req['category_id']);

        $key = md5($employee_id . $start_date . $end_date . $category_id);

        if (Cache::is_brute_force("timerecordsAdd-" . $key, [
            "minute" => 20,
            "hour" => 50,
            "day" => 100,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("timerecordsAdd-" . Req::getServer("REMOTE_ADDR"), [
            "minute" => 40,
            "hour" => 300,
            "day" => 900,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } else {
            if ($permissions['timerecords_create']['allow']) {
                $new_id = TimeRecords::getNewId();
                $insert = [
                    "id" => (int)$new_id,
                    "creator_id" => (string)Auth::getData()->_id,
                    "employee_id" => (string)$employee_id,
                    "start_date" => $start_date ? TimeZones::date(strtotime($start_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]) : null,
                    "end_date" => $end_date ? TimeZones::date(strtotime($end_date), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]) : null,
                    "category_id" => (string)$category_id,
                    "status" => 0,
                    "is_deleted" => 0,
                    "created_at" => TimeRecords::getDate(),
                ];

                $insert_id = TimeRecords::insert($insert);

                $response = array(
                    "status" => "success",
                    "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                );

                // Log start
                Activities::log([
                    "user_id" => (string)Auth::getData()->_id,
                    "section" => "timerecords",
                    "operation" => "timerecords_create",
                    "values" => [
                        "id" => $insert_id,
                    ],
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
