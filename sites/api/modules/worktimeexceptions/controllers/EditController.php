<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\WorkTimeExceptions;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error      = false;
        $req        = (array) Req::get();
        $id         = (string) $req['id'];
        $user_id    = (string) trim($req['user_id']);
        $start_date = (string) trim($req['start_date']);
        $end_date   = (string) trim($req['end_date']);
        $category_id   = (string) trim($req['category_id']);
        $description   = (string) trim($req['description']);

        $data = WorkTimeExceptions::findFirst([
            [
                "_id"        => WorkTimeExceptions::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (Cache::is_brute_force("worktimeexceptions-edit-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['worktimeexceptions_update']['allow']) {
                $update = [
                    "user_id"    => (string) $user_id,
                    "category_id"    => (string) $category_id,
                    "description"    => (string) $description,
                    "start_date" => WorkTimeExceptions::getDate(strtotime($start_date)),
                    "end_date"   => WorkTimeExceptions::getDate(strtotime($end_date)),
                    "updated_at" => WorkTimeExceptions::getDate(),
                ];

                WorkTimeExceptions::update(["_id" => $data->_id], $update);
                $response = [
                    "status"      => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                ];

                // Log start
                Activities::log([
                    "user_id"   => (string)Auth::getData()->_id,
                    "section"   => "worktimeexceptions",
                    "operation" => "worktimeexceptions_update",
                    "values"    => [
                        "id" => $data->_id,
                    ],
                    "oldObject" => $data,
                    "newObject" => WorkTimeExceptions::findById($data->_id),
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
        echo json_encode((object) $response);
        exit;
    }
}
