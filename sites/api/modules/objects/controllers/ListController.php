<?php


namespace Controllers;


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
        if ($limit == 0)
            $limit = 50;
        if ($limit > 200)
            $limit = 200;
        //check Permission start
        $allow = false;
        if ($permissions['objects_view']['allow']) {
            $allow = true;
        }


        $binds = [
            "is_deleted" => 0,
        ];

        if (in_array("all", $permissions['objects_view']['selected'])) {
            $allow =true;
        } elseif (in_array("self", $permissions['objects_view']['selected'])) {
            $binds["users"] =  (string)Auth::getData()->_id;
            $allow =true;
        }


        //check Permission end

        $ids = [];
        foreach (Req::get("ids") as $value)
            if ($value > 0)
                $ids[] = (int)$value;
        if (count($ids) > 0)
            $binds["id"] = [
                '$in' => $ids
            ];


        $query = Objects::find([
            $binds,
            "skip" => $skip,
            "limit" => $limit,
            "sort" => [
                "_id" => 1
            ]
        ]);

        $data = [];
        if(!$allow){
            $error = Lang::get("PageNotAllowed");

        }
        elseif (count($query) > 0) {


            foreach ($query as $value) {
                $data[] = Objects::filterData(Lang::getLang(), $value);
            }

            $response = array(
                "status" => "success",
                "data" => $data,
            );
        } else {
            $error = Lang::get("uDontHaveObj", "Object not found");
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