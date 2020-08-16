<?php
namespace Controllers;



use Custom\Models\GeoObjects;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Lib\TimeZones;

class ListController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error      = false;
        $skip 		= (int)Req::get("skip");
        $limit 		= (int)Req::get("limit");
        $type 		= (string)Req::get("type");
        if($limit == 0)
            $limit = 50;
        if($limit > 200)
            $limit = 200;
        //check Permission start
        $allow = false;
        if ($permissions['objects_view']['allow']) {
            $allow = true;
        }


        $binds = [
            "is_deleted" => 0,
        ];

        if (in_array("all", $permissions['geoobjects_view']['selected'])) {
            $allow =true;
        } elseif (in_array("self", $permissions['geoobjects_view']['selected'])) {
            $binds["user_id"] =  (string)Auth::getData()->_id;
            $allow =true;
        }else{
            $allow =false;
        }


        //check Permission end


        if($type == "geozone"){
            $binds["type"] = [
                '$in' => ["circle", "polygon"]
            ];
        }
        elseif ($type == "geopoint"){
            $binds["type"] = [
                '$in' => ["marker"]
            ];
        }
        elseif(strlen($type) > 0)
            $binds["type"] = $type;

        $query		= GeoObjects::find([
            $binds,
            "skip"	=> $skip,
            "limit"	=> $limit,
            "sort"	=> [
                "_id"	=> 1
            ]
        ]);
        $data 		= [];
        if(!$allow){
            $error = Lang::get("PageNotAllowed");

        }
        elseif(count($query) > 0)
        {
            foreach($query as $value)
            {
                $data[] = [
                    "id"			=> (string)$value->_id,
                    "type"			=> (string)$value->type,
                    "title"			=> (string)$value->title,
                    "coordinates"	=> (string)$value->type == "polygon" ? $value->geometry->coordinates[0]: $value->geometry->coordinates,
                    "radius"		=> (float)@$value->radius,
                    "created_at"	=> TimeZones::date($value->created_at, "Y-m-d H:i:s")
                ];
            }

            $response = [
                "status" 		=> "success",
                "data"			=> $data,
            ];
        }
        else
        {
            $error = Lang::get("uDontHaveObj", "Object not found");
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
