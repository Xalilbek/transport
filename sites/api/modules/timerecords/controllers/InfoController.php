<?php

namespace Controllers;

use Custom\Models\Parameters;
use Custom\Models\TimeRecords;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id = (string)Req::get("id");
        $data = TimeRecords::findFirst([
            [
                "_id" => TimeRecords::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['timerecords_view']['allow']) {
                $employee = Users::getByMongoId(Users::objectId($data->employee_id));
                $category = Parameters::findById($data->category_id);

                $response = [
                    "status" => "success",
                    "data" => [
                        'id' => (string)$data->_id,
                        'start_date' => $data->start_date ? TimeZones::date($data->start_date, "Y-m-d H:i") : "----:--:-- --:--",
                        'end_date' => $data->end_date ? TimeZones::date($data->end_date, "Y-m-d H:i") : "----:--:-- --:--",
                        'category_id' => (string)$data->category_id,
                        'employee' => ($employee ? [
                            "id" => (string)$employee->_id,
                            "fullname" => $employee->fullname,
                        ] : [
                            "id" => 0,
                            "fullname" => Lang::get("Deleted"),
                        ]),
                        'category' => ($category ? [
                            "id" => (string)$category->_id,
                            "title" => Parameters::getTitleByLang($category, Lang::getLang()),
                        ] : [
                            "id" => 0,
                            "title" => "null",
                        ]),
                        'created_at' => TimeZones::date($data->created_at, "Y-m-d H:i"),
                    ],
                ];
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
