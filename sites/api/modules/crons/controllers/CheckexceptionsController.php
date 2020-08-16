<?php
namespace Controllers;

use Custom\Models\UserVehicles;
use Custom\Models\WorkTimeExceptions;
use Lib\Req;

class CheckexceptionsController extends \Phalcon\Mvc\Controller
{
    public static $business_id = 0;
    public function initialize()
    {
        $business_id = (int) Req::get("business_id");
        if ($business_id) {
            self::$business_id = $business_id;
        }
    }

    public function indexAction()
    {
        $error = false;
        $req   = (array) Req::get();
        $data  = [];

        $startTime = strtotime(date("Y-m-d 00:00:00")) + 3 * 86400;

        $exceptions = WorkTimeExceptions::find([
            [
                /*"last_check_time" => [
                    '$gt' => WorkTimeExceptions::getDate(time() - 7200),
                ],*/
                "start_date"  => [
                    '$lt' => WorkTimeExceptions::getDate($startTime),
                    '$gte'  => WorkTimeExceptions::getDate(strtotime(date("Y-m-d 00:00:00"))),
                ],
                "is_deleted"  => [
                    '$ne' => 1,
                ],
            ],
            "sort" => [
                "start_date" => 1,
            ],
            "limit" => 1
        ]);

        $userVehicleIds       = [];
        $exceptionsByUserId[] = [];

        $userIds = [];
        foreach ($exceptions as $row) {
            if (!in_array($row->user_id, $userIds)) {
                $userIds[] = $row->user_id;
            }
            $exceptionsByUserId[$row->user_id][] = $row;
        }

        $userVehicles = UserVehicles::find([
            [
                "user_id"     => [
                    '$in' => $userIds,
                ],
                "is_deleted"  => [
                    '$ne' => 1,
                ],
            ],
        ]);

        foreach ($userVehicles as $row) {
            foreach ($exceptionsByUserId[$row->user_id] as $exp) {
                if (strtotime(date("Y-m-d 00:00:00")) >= UserVehicles::toSeconds($exp->start_date)) {
                    UserVehicles::update([
                        "_id"         => $row->_id,
                    ], [
                        "is_deleted" => 1,
                        "deleted_at" => UserVehicles::getDate(),
                        "last_check_time" => UserVehicles::getDate()
                    ]);
                    $data[] = $row;
                } elseif(UserVehicles::toSeconds($exp->start_date) - strtotime(date("Y-m-d 00:00:00")) <= 86400) {
                    if ($row->status != 3) {
                        UserVehicles::update([
                            "_id"         => $row->_id,
                        ], [
                            "status"     => 3,
                            "updated_at" => UserVehicles::getDate(),
                            "last_check_time" => UserVehicles::getDate()
                        ]);
                        $data[] = [
                            UserVehicles::toSeconds($exp->start_date),
                            strtotime(date("Y-m-d 00:00:00"))
                        ];
                    }
                } else {
                    if ($row->status != 2) {
                        UserVehicles::update([
                            "_id"         => $row->_id,
                        ], [
                            "status"     => 2,
                            "updated_at" => UserVehicles::getDate(),
                            "last_check_time" => UserVehicles::getDate()
                        ]);
                        $data[] = $row;
                    }
                }
            }
        }

        echo '<pre>';
        print_r($data);
        exit;

        $response = array(
            "status" => "success",
            "data"   => $data,
        );

        if ($error) {
            $response = array(
                "status"      => "error",
                "description" => $error,
            );
        }
        echo json_encode($response, true);
        exit();
    }
}
