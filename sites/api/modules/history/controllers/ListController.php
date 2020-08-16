<?php

namespace Controllers;

use Custom\Models\History;
use Custom\Models\Objects;
use Lib\Auth;
use Lib\Helper;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;
use Lib\TimeZones;

class ListController extends \Phalcon\Mvc\Controller{

    public function indexAction(){
        ini_set('memory_limit','256M');
        $permissions = Auth::getPermissions();

        $error      = false;
        $id 		= (string)Req::get("id");

        $binds = [
            "_id"	=> Objects::objectId($id),

        ];

        if ($permissions['tracking_history_view']['allow']) {
            $allow = true;
        }

        if (in_array("all", $permissions['tracking_history_view']['selected'])) {
            $allow =true;
        } elseif (in_array("self", $permissions['tracking_history_view']['selected'])) {
            $binds["users"] =  (string)Auth::getData()->_id;
            $allow =true;
        }



        $object = Objects::findFirst([
            $binds
        ]);

        $dateFrom 	= strtotime(Req::get("datefrom"));
        $dateTo 	= strtotime(Req::get("dateto"));
        if(!$allow){
            $error = Lang::get("PageNotAllowed");
        }
        elseif(!$dateFrom || !$dateTo)
        {
            $error = Lang::get("dateIntervalWrong", "Date interval is wrong");
        }
        elseif($object)
        {

            $binds = [
                "object_id"		=> (string)$object->_id,
                "business_id" =>$object->business_id,

                "started_at"		=> [
                    '$gt' 	=> TimeZones::date(strtotime($dateFrom), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]),
                    '$lte' 	=> TimeZones::date(strtotime($dateTo), "mongo", ["tzfrom" => USER_TIMEZONE, "tzto" => DEFAULT_TIMEZONE]),
                ],
            ];
            $historyQuery = History::find([
                $binds,
                "sort"	=> [
                    "_id"	=> -1
                ]
            ]);
//            var_dump($historyQuery);

            $historyIds = [];
            foreach($historyQuery as $value)
                $historyIds[] = (string)$value->_id;

            if(count($historyQuery) > 0)
            {
                foreach($historyQuery as $value)
                {


                    $history = [
                        "id"			=> (string)$value->_id,
                        "type"			=> $value->action == "parking" ? "point": "route",
                        "action"		=> $value->action == "move" ? "move": "parking",
                        "starttime"		=> TimeZones::date($value->started_at, "d/m/Y H:i:s"),
                        "endtime"		=> ($value->ended_at) ? TimeZones::date($value->ended_at, "d/m/Y H:i:s"): false,
                        "history_date"  => History::secondsToTime(Lang::getLang(), History::toSeconds($value->started_at)),
                        "duration"		=> Lib::durationToStr( $value->duration,Lang::getLang()),
                        "distance"		=> round($value->distance/1000, 2)." km",
                        "finished"		=> ($value->ended_at) ? true: false,
                    ];

                    if($value->action == "parking")
                    {
                        list($lon, $lat) = Objects::getLonLatFromGeometry($value->geometry);
                        $history["coords"] = [
                            "longitude"		=> $lon,
                            "latitude"		=> $lat,
                        ];
                    }

                    $data[] = $history;
                }

                $response = [
                    "status" 		=> "success",
                    "data"			=> $data,
                ];
            }
            else
            {
                $error = Lang::get("noInformation", "No information");
            }
        }
        else
        {
            $error = Lang::get("uDontHaveObj", "Object not found");
        }


        if($error)
        {
            $response = [
                "status" 		=> "error",
                "error_code"	=> 1023,
                "description" 	=> $error,
            ];
        }
        echo json_encode($response, true);
        exit();
    }

}