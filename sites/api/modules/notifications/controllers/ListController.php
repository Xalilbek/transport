<?php

namespace Controllers;


use Custom\Models\Alerts;
use Custom\Models\Notifications;
use Custom\Models\Objects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class ListController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $skip = (int)Req::get("skip");
        $limit = (int)Req::get("limit");
        $id = (string)Req::get("object_id");
        if ($limit == 0)
            $limit = 50;
        if ($limit > 200)
            $limit = 200;

        $allow = false;
        if ($permissions['notifications_view']['allow']) {
            $allow = true;
        }
        $binds = [
            //"user_id"		=> (int)Auth::getData()->id,
        ];

        $bindsForObjects = [];

        if (in_array("all", $permissions['notifications_view']['selected'])) {
            $allow =true;
        } elseif (in_array("self", $permissions['notifications_view']['selected'])) {
            $bindsForObjects["users"] =  (string)Auth::getData()->_id;
            $allow =true;
        }

        $objectsQuery = Objects::find([
            $bindsForObjects
        ]);

        $objectIds = [];
        $objectsData = [];
        foreach ($objectsQuery as $value) {
            $objectIds[] = (string)$value->_id;
            $objectsData[(string)$value->_id] = $value;
        }

        if ($id) {
            $binds["object_id"] = (string)$id;
        } else {
            $binds["object_id"] = ['$in' => $objectIds];
        }

        $query = Notifications::find([
            $binds,
            "skip" => $skip,
            "limit" => $limit,
            "sort" => [
                "_id" => -1
            ]
        ]);

        $count = Notifications::count(
            [
                $binds
            ]
        );


        $data = [];
        if(!$allow){
            $error = Lang::get("PageNotAllowed");

        }
        elseif (count($query) > 0) {
            foreach ($query as $value) {
                $data[] = Notifications::filterData($this->lang, $value, Alerts::getTypes($this->lang, true), $objectsData[$value->object_id]);
            }

            $response = array(
                "status" => "success",
                "count" => $count,
                "data" => $data,
            );
        } else {
            $error = Lang::get("noInformation", "No information found");
        }

        if ($error) {
            $response = array(
                "status" => "error",
                "error_code" => 1023,
                "description" => $error,
            );
        }
        echo json_encode($response, true);
        exit();
    }

}