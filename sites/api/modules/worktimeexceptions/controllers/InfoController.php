<?php
namespace Controllers;

use Custom\Models\Users;
use Custom\Models\WorkTimeExceptions;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Models\Parameters;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $id    = (string) Req::get("id");
        $data  = WorkTimeExceptions::findFirst([
            [
                "_id"        => WorkTimeExceptions::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['worktimeexceptions_view']['allow']) {
                $user     = Users::getByMongoId(Users::objectId($data->user_id));
                $category     = Parameters::findById($data->category_id);
                $response = [
                    "status" => "success",
                    "data"   => [
                        'id'         => (string) $data->_id,
                        'user'       => $user ? [
                            "id"       => $user->id,
                            "fullname" => $user->fullname,
                        ] : [
                            "id"       => 0,
                            "fullname" => Lang::get("Deleted"),
                        ],
                        'description'         => (string) $data->description,
                        'category'    => [
                            "id" => (string)$category->_id,
                            "title" => (string)Parameters::getTitleByLang($category, Lang::getLang())
                        ],
                        'start_date' => WorkTimeExceptions::dateFormat($data->start_date, "Y-m-d"),
                        'end_date'   => WorkTimeExceptions::dateFormat($data->end_date, "Y-m-d"),
                        'created_at' => WorkTimeExceptions::dateFormat($data->created_at, "Y-m-d H:i"),
                    ],
                ];
            } else {
                $error = Lang::get("PermissionsDenied");
            }
        }
        if ($error) {
            $response = [
                "status"      => "error",
                "error_code"  => 1017,
                "description" => $error,
            ];
        }
        echo json_encode((object) $response);
        exit;
    }
}
