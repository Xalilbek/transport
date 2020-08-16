<?php

namespace Controllers;

use Custom\Models\Alerts;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class DeleteController extends \Phalcon\Mvc\Controller
{
    public function indexAction(){
        $error 		= false;
        $id 		= (string)Req::get("id");
        if(strlen($id) > 0)
            $data 		= Alerts::findFirst([
                [
                    "_id" 			=> Alerts::objectId($id),
                    "user_id"		=> (string)Auth::getData()->_id,
                    "is_deleted"	=> 0,
                ]
            ]);

        if (!$data)
        {
            $error = Lang::get("AlertNotFound", "Alert doesn't exist");
        }
        elseif (!Auth::checkPermission(Lang::getLang(), 'notifications_settings_delete',$data->user_id )) {
            $error = Lang::get("PageNotAllowed");
        }
        else
        {
            $update = [
                "is_deleted"	=> 1,
                "deleted_at"	=> Alerts::getDate()
            ];
            Alerts::update(["_id" 			=> Alerts::objectId($id)], $update);

            $response = [
                "status" 		=> "success",
                "description" 	=> Lang::get("DeletedSuccessfully", "Deleted successfully")
            ];
        }

        if($error)
        {
            $response = [
                "status" 		=> "error",
                "error_code"	=> 1017,
                "description" 	=> $error,
            ];
        }
        echo json_encode((object)$response);
        exit;
    }

}