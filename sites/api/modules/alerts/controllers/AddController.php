<?php

namespace Controllers;

use Custom\Models\Alerts;
use Custom\Models\Cache;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class AddController extends \Phalcon\Mvc\Controller{

    public function indexAction(){
        $error 			= false;
        $title			= str_replace(["<",">",'"',"'"], "", trim(urldecode(Req::get("title"))));
        $type 			= (int)Req::get("type");

        $object_ids = [];
        foreach(Req::get("object_ids") as $value)
            $object_ids[] = (string)$value;



        $geozone_ids = [];
        foreach(Req::get("geozone_ids") as $value)
            $geozone_ids[] = (string)$value;

        $geopoint_ids = [];
        foreach(Req::get("geopoint_ids") as $value)
            $geopoint_ids[] = (string)$value;
        $radius 		= (int)Req::get("radius");
        $speed 			= (int)Req::get("speed");
        $text 			= (string)trim(Req::get("text"));

        if (!Auth::checkPermission(Lang::getLang(), 'notifications_settings_create',(string)Auth::getData()->_id )) {
            $error = Lang::get("PageNotAllowed");
        }
        elseif(Cache::is_brute_force("objAdd-".Req::getServer("REMOTE_ADDR"), ["minute"	=> 200, "hour" => 500, "day" => 2000]))
        {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        }
        elseif(strlen($title) < 2 || strlen($title) > 100)
        {
            $error = Lang::get("TitleError", "Title is wrong. (minimum 2 and maximum 100 characters)");
        }
        elseif($type < 1)
        {
            $error = Lang::get("AlertTypeError", "Alert type is wrong");
        }

        elseif(in_array($type, [1,2,3,4]) && (count($geozone_ids) == 0 && count($geopoint_ids) == 0))
        {
            $error = Lang::get("GeozonesEmpty", "Geozones / Geopoints are empty");
        }
        elseif(in_array($type, [3,4]) && $radius < 1)
        {
            $error = Lang::get("RadiusIsWrong", "Radius is wrong");
        }
        elseif(in_array($type, [5]) && $speed < 1)
        {
            $error = Lang::get("SpeedIsWrong", "Speed is wrong");
        }
        //elseif(strlen($text) < 2 || strlen($text) > 100)
        //{
        //	$error = Lang::get("AlertTextError", "Alert text is wrong. (minimum 2 and maximum 100 characters)");
        //}
        else
        {
            $id = (int)Alerts::getNewId();
            $userInsert = [
                "id"				=> $id,
                "title"				=> $title,
                "user_id" 			=> (string)Auth::getData()->_id,
                "type" 				=> $type,
                "object_ids" 		=> $object_ids,
                "geozone_ids" 		=> $geozone_ids,
                "geopoint_ids" 		=> $geopoint_ids,
                "radius" 			=> $radius,
                "speed" 			=> $speed,
                "text" 				=> $text,
                "is_deleted"		=> 0,
                "business_id"       => BUSINESS_ID,
                "created_at"		=> Alerts::getDate()
            ];

            Alerts::insert($userInsert);


            $response = array(
                "status" 		=> "success",
                "description" 	=> Lang::get("AddedSuccessfully", "Added successfully"),
            );
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