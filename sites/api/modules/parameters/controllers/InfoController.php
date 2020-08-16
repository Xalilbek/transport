<?php

namespace Controllers;

use Custom\Models\Parameters;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class InfoController extends \Phalcon\Mvc\Controller
{
    public function getAccess($type)
    {
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
        return $allow;
    }

    public function indexAction()
    {
        $error = false;
        $response = [];
        $req = (array)Req::get();
        $id = (string)$req["id"];
        $data = Parameters::findFirst([
            [
                "_id" => Parameters::objectId($id),
                "is_deleted" => [
                    '$ne' => 1,
                ],
            ],
        ]);

        if (!$data) {
            $error = Lang::get("NoInformation", "Information not found");
        } elseif (!$this->getAccess($data->type)) {
            $error = Lang::get("PageNotAllowed");
        } else {

            $params = [
                "id" => (string)$data->_id,
                "type" => $data->type,
                "category" => $data->category,
                "parent_id" => $data->parent_id,
                "titles" => $data->titles,
                "index" => $data->index,
                "active" => $data->active,
                "default_lang" => $data->default_lang,
                "slug" => $data->slug,
            ];

            if (in_array($data->type, ["time_record_categories"])) {
                $params = array_merge($params, [
                    "work_type" => $data->work_type,
                ]);
            }

            if (in_array($data->type, ["price_list"])) {
                $params = array_merge($params, [
                    "price" => $data->price,
                    "weight" => $data->weight
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

            $response = array(
                "status" => "success",
                "description" => "",
                "data" => $params,
            );
        }

        if ($error) {
            $response = [
                "status" => "error",
                "error_code" => 1017,
                "description" => $error,
            ];
        }
        echo json_encode($response);
        exit;
    }

}
