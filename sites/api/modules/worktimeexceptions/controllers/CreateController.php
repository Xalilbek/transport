<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\WorkTimeExceptions;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class CreateController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $user_id     = (string) trim($req['user_id']);
        $start_date  = (string) trim($req['start_date']);
        $end_date    = (string) trim($req['end_date']);
        $category_id = (string) trim($req['category_id']);
        $description = (string) trim($req['description']);

        $key = md5($user_id);

        if (Cache::is_brute_force("exceptions-add-" . $key, [
            "minute" => 20,
            "hour"   => 50,
            "day"    => 100,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("exceptions-add-" . Req::getServer("REMOTE_ADDR"), [
            "minute" => 40,
            "hour"   => 300,
            "day"    => 900,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } else {
            if ($permissions['worktimeexceptions_create']['allow']) {
                $new_id = WorkTimeExceptions::getNewId();
                $insert = [
                    "id"          => (int) $new_id,
                    "creator_id"  => (string) Auth::getData()->_id,
                    "user_id"     => (string) $user_id,
                    "category_id" => (string) $category_id,
                    "description" => (string) $description,
                    "start_date"  => WorkTimeExceptions::getDate(strtotime($start_date)),
                    "end_date"    => WorkTimeExceptions::getDate(strtotime($end_date)),
                    "created_at"  => WorkTimeExceptions::getDate(),
                ];

                $insert_id = WorkTimeExceptions::insert($insert);

                $response = array(
                    "status"      => "success",
                    "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                );

                // Log start
                Activities::log([
                    "user_id"   => (string)Auth::getData()->_id,
                    "section"   => "worktimeexceptions",
                    "operation" => "worktimeexceptions_create",
                    "values"    => [
                        "id" => $insert_id,
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
        echo json_encode((object) $response);
        exit;
    }
}
