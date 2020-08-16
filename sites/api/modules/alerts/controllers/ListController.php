<?php

namespace Controllers;

use Custom\Models\Alerts;
use Custom\Models\Notifications;
use Custom\Models\Objects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class ListController extends \Phalcon\Mvc\Controller{

    public function indexAction(){
        $permissions = Auth::getPermissions();

        $error      = false;
        $skip 		= (int)Req::get("skip");
        $limit 		= (int)Req::get("limit");
        if($limit == 0)
            $limit = 50;
        if($limit > 200)
            $limit = 200;

        $binds = [
            "is_deleted"	=> 0,
        ];
        $allow = false;
        if ($permissions['objects_view']['allow']) {
            $allow = true;
        }

        if (in_array("all", $permissions['notifications_settings_view']['selected'])) {
            $allow =true;
        } elseif (in_array("self", $permissions['notifications_settings_view']['selected'])) {
            $binds["user_id"] =  (string)Auth::getData()->_id;
            $allow =true;
        }

        $query		= Alerts::find([
            $binds,
            "skip"	=> $skip,
            "limit"	=> $limit,
            "sort"	=> [
                "_id"	=> 1
            ]
        ]);

        $count = Alerts::count([
            $binds,
        ]);

        $data 		= [];
        if(!$allow){
            $error = Lang::get("PageNotAllowed");

        }
        elseif(count($query) > 0)
        {
            foreach($query as $value)
            {
                $data[] = Alerts::filterData(Lang::getLang(), $value, Alerts::getTypes(Lang::getLang(), true));
            }

            $response = array(
                "status" 		=> "success",
                "count"         => $count,
                "data"			=> $data,
            );
        }
        else
        {
            $error = Lang::get("noInformation", "No information found");
        }

        if($error)
        {
            $response = array(
                "status" 		=> "error",
                "error_code"	=> 1023,
                "description" 	=> $error,
            );
        }
        echo json_encode($response, true);
        exit();
    }
}