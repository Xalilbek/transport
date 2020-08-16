<?php

namespace Controllers;

use Custom\Models\Parameters;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class ListController extends \Phalcon\Mvc\Controller
{
    public function initialize()
    {
        $type = trim(Req::get("type"));
        $allow = false;
        $permissions = Auth::getPermissions();
        if ($permissions['parameters_view']['allow']) {
            if ($permissions['parameters_view']['all']) {
                $allow = true;
            } else {
                foreach (Parameters::typeListByKey() as $key => $value) {
                    if ($type == $key && in_array($key, $permissions['parameters_view']['selected'])) {
                        $allow = true;
                    }
                }
            }
        }
        if (!$allow) {
            echo json_encode(array(
                "status" => "error",
                "error_code" => 1023,
                "description" => Lang::get("PageNotAllowed"),
            ), true);
            exit();
        }
    }

    public function getChildren( $arr, $arrData, $id)
    {
        $data = $arrData[$id];

        $children = [];
        foreach ($arr[$id] as $cid) {
            $child = $this->getChildren( $arr, $arrData, $cid);
            $children[] = $child;
        }
        if (count($children) == 0) {
            $children = false;
        }

        $params = [
            "id" => (string)$data->_id,
            "id_numeric" => $data->id,
            "type" => $data->type,
            "category" => $data->category,
            "parent_id" => $data->parent_id,
            "title" => Parameters::getTitleByLang($data, Lang::getLang()),
            "index" => $data->index,
            "active" => $data->active,
            "default_lang" => $data->default_lang,
            "slug" => $data->slug,
            "children" => $children,
        ];

        if (in_array($data->type, ["time_record_categories"])) {
            $params = array_merge($params, [
                "work_type" => $data->work_type,
            ]);
        }

        if (in_array($data->type, ["price_list"])) {
            $params = array_merge($params, [
                "price" => $data->price,
                "weight" => $data->Weight
            ]);
        }

        if (in_array($data->type, ["currencies"])) {
            $params = array_merge($params, [
                "currency_rate" => $data->currency_rate,
                "currency_slug" => $data->currency_slug,
                "currency_symbol" => $data->currency_symbol,
            ]);
        }

        if (in_array($data->type, ["timezones"])) {
            $params = array_merge($params, [
                "gmt_offset" => $data->gmt_offset,
                "has_daylight_saving" => $data->has_daylight_saving,
                "timezone_slug" => $data->timezone_slug,
                "summer_daylight_date" => $data->summer_daylight_date,
                "winter_daylight_date" => $data->winter_daylight_date,
            ]);
        }

        return $params;
    }

    public function indexAction()
    {
        $error = false;
        $req = (array)Req::get();

        $title = (string)trim($req["title"]);
        $active = (string)trim($req["active"]);
        $type = (string)trim($req["type"]);

        $skip = (int)$req["skip"];
        $limit = (int)$req["limit"];

        if ($limit == 0) {
            $limit = 50;
        } else if ($limit > 200) {
            $limit = 200;
        }

        $binds = [
            "is_deleted" => [
                '$ne' => 1,
            ],
        ];

        if (strlen($type) > 0) {
            $binds["type"] = (string)$type;
        }

        if (in_array($active, ["1", "0"])) {
            $binds["active"] = (int)$active;
        }

        if (strlen($title) > 0) {
            $binds["titles." . Lang::getLang()] = [
                '$regex' => trim($title),
                '$options' => 'i',
            ];
        }

        $sort_field = trim($req["sort"]);
        $sort_order = trim($req["sort_type"]);

        $sort = ["index" => 1];
        if (in_array($sort_field, ['title', 'active'])) {
            $sort[strtr($sort_field, [
                'title' => 'titles.' . Lang::getLang(),
            ])] = $sort_order == 'desc' ? -1 : 1;
        }

        $conditions = [
            $binds,
            "sort" => $sort,
        ];

        if (Req::get("limit") !== "-1") {
            $conditions = array_merge($conditions, [
                "limit" => $limit,
                "skip" => $skip,
            ]);
        }

        $query = Parameters::find($conditions);
        $count = Parameters::count([$binds]);

        $data = [];
        if (count($query) > 0) {
            $arr = [];
            $arrData = [];
            foreach ($query as $value) {
                $arr[(string)$value->parent_id][] = (string)$value->_id;
                $arrData[(string)$value->_id] = $value;
            }

            foreach ($arr["0"] as $cid) {
                $child = $this->getChildren($arr, $arrData, $cid);
                if ($child)
                    $data[] = $child;
            }

            $response = [
                "status" => "success",
                "limit" => $limit,
                "skip" => $skip,
                "count" => $count,
                "data" => $data,
            ];
        } else {
            $error = Lang::get("noInformation", "Information not found");
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
