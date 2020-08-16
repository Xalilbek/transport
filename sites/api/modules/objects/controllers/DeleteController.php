<?php

namespace Controllers;

use Custom\Models\Objects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class DeleteController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {

        $error 		= false;
        $id 		= (string)Req::get("id");
        $data 		= Objects::findFirst([
            [
                "_id" 			=> Objects::objectId($id),
                "users"			=> (string)Auth::getData()->_id,
                "is_deleted"	=> 0,
            ]
        ]);

        if (!$data)
        {
            $error = Lang::get("ObjectNotFound", "Object doesn't exist");
        }
        elseif (!Auth::checkPermission(Lang::getLang(), 'objects_delete',$data->owner_id )) {
            $error = Lang::get("PageNotAllowed");
        }
        else
        {
            $update = [
                "is_deleted"	=> 1,
                "owner_id"		=> 0,
                "deleted_at"	=> Objects::getDate(),
                "deleter_id"	=> (string)Auth::getData()->_id
            ];
            Objects::update(["_id"	=> Objects::objectId($id)], $update);

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