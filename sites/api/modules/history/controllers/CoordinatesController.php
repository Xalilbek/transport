<?php

namespace Controllers;

use Custom\Models\Alerts;
use Custom\Models\History;
use Custom\Models\LogsTracking;
use Custom\Models\Notifications;
use Custom\Models\Objects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class CoordinatesController extends \Phalcon\Mvc\Controller{
    public function indexAction(){
        ini_set('memory_limit','256M');
        $permissions = Auth::getPermissions();

        //check Permission start
        $allowAll = false;
        $allowSelf = false;
        if ($permissions['tracking_history_view']['allow']) {
            $allowAll = true;
            $allowSelf = true;
        } if (in_array("all", $permissions['tracking_history_view']['selected'])) {
            $allowAll = true;
            $allowSelf = true;
        } elseif (in_array("self", $permissions['tracking_history_view']['selected'])) {
            $allowSelf =true;
        }


        $history_id 		= (string)Req::get("id");
        $notifications 		= [];

        if(strlen($history_id) > 0){
            $query = LogsTracking::find([
                [
                    "history_id"	=> $history_id,
                    "business_id" =>BUSINESS_ID
                ],
                "sort"	=> [
                    "unixtime"	=> -1
                ],
                //"limit"	=> 100
            ]);

            $notQuery = Notifications::find(
                [
                    [
                        "history_id"	=> $history_id,
                    ],
                ]
            );
            foreach($notQuery as $value){
                $notif = Notifications::filterData(Lang::getLang(), $value, Alerts::getTypes(Lang::getLang(), true));
                $notif["coordinates"] = Objects::getLonLatFromGeometry($value->geometry);
                $notifications[] = $notif;
            }
        }else{

            $query = [];

        }


        $data = [];
        foreach($query as $value)
        {

            if ($allowAll || ($allowSelf && $value->users = (string)Auth::getData()->_id )){
                list($lon, $lat) = Objects::getLonLatFromGeometry($value->geometry);
                $data[] = [
                    $lon, $lat, (int)$value->angle
                ];
            }

        }
        if(count($data) > 0)
        {
            $response = [
                "status" 		=> "success",
                "data" 			=> $data,
                "notifications" => $notifications,
            ];
        }
        else
        {
            $response = [
                "status" 		=> "error",
                "error_code"	=> 1023,
                "description" 	=> "No data",
            ];
        }
        echo json_encode($response, true);
        exit();
    }
}