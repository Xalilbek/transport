<?php
namespace Controllers;

use Custom\Models\WorkTimeExceptions;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class DeleteController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = Req::get();
        $id    = (string) $req["id"];
        $data  = WorkTimeExceptions::findFirst([
            [
                "_id"        => WorkTimeExceptions::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['worktimeexceptions_delete']['allow']) {
                $update = [
                    "is_deleted" => 1,
                    "deleter_id" => (string)Auth::getData()->_id,
                    "deleted_at" => WorkTimeExceptions::getDate(),
                ];
                WorkTimeExceptions::update(["_id" => $data->_id], $update);
                $response = [
                    "status"      => "success",
                    "description" => Lang::get("DeletedSuccessfully", "Deleted successfully"),
                ];
                // Log start
                Activities::log([
                    "user_id"   => (string)Auth::getData()->_id,
                    "section"   => "worktimeexceptions",
                    "operation" => "worktimeexceptions_delete",
                    "values"    => [
                        "id" => $data->_id,
                    ],
                    "status"    => 1,
                ]);
                // Log end
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
        echo json_encode($response);
        exit;
    }
}
