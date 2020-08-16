<?php

namespace Controllers;

use Custom\Models\GeoObjects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class DeleteController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $data 		= false;
        $error 		= false;
        $id 		= (string)Req::get("id");
        if(strlen($id) > 0)
            $data 		= GeoObjects::findFirst([
                [
                    "_id" 			=> GeoObjects::objectId($id),
                    "user_id"		=> (string)Auth::getData()->_id,
                    "is_deleted"	=> 0,
                ]
            ]);

        if (!$data)
        {
            $error = Lang::get("ObjectNotFound", "Object doesn't exist");
        }
        elseif (!Auth::checkPermission($this->lang, 'geoobjects_delete',$data->user_id )) {
            $error = Lang::get("PageNotAllowed");
        }
        else
        {
            $update = [
                "is_deleted"	=> 1,
                "deleter_id"	=> (string)Auth::getData()->_id,
                "deleter_at"	=> GeoObjects::getDate(),
            ];
            GeoObjects::update(["_id"	=> $data->_id], $update);

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
        echo json_encode($response);
        exit;
    }
}