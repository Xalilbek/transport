<?php

namespace Controllers;

use Custom\Models\Parameters;
use Lib\Lang;
use Lib\Req;

class MinlistController extends \Phalcon\Mvc\Controller
{
    public function getChildren( $arr, $arrData, $id)
    {
        $data = $arrData[$id];

        $children = [];
        foreach ($arr[$id] as $cid) {
            $child = $this->getChildren( $arr, $arrData, $cid);
            if ($child) {
                $children[] = $child;
            }
        }
        if (count($children) == 0) {
            $children = false;
        }

        $data = $arrData[$id];
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
                "weight" => $data->weight,
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
        $parent_id = (string)trim($req["parent_id"]) ? trim($req["parent_id"]) : 0;

        $binds = [
            "is_deleted" => [
                '$ne' => 1,
            ],
        ];

        if (strlen($parent_id) > 1) {
            $binds["parent_id"] = (string)$parent_id;
        }

        if (strlen($type) > 0) {
            $binds["type"] = (string)$type;
        }

        if (in_array($active, [
            "1",
            "0",
        ])) {
            $binds["active"] = (int)$active;
        }

        if (strlen($title) > 0) {
            $binds["titles." . Lang::getLang()] = [
                '$regex' => trim($title),
                '$options' => 'i',
            ];
        }

        $conditions = [
            $binds
        ];

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

            foreach ($arr[$parent_id] as $cid) {
                $child = $this->getChildren($arr, $arrData, $cid);
                if ($child) {
                    $data[] = $child;
                }
            }

            $response = [
                "status" => "success",
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
