<?php

namespace Controllers;

use Custom\Models\Parameters;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class AddController extends \Phalcon\Mvc\Controller
{
    public function initialize()
    {
        $type = trim(Req::get("type"));
        $allow = false;
        $permissions = Auth::getPermissions();
        if ($permissions['parameters_create']['allow']) {
            if ($permissions['parameters_create']['all']) {
                $allow = true;
            } else {
                foreach (Parameters::typeListByKey() as $key => $value) {
                    if ($type == $key && in_array($key, $permissions['parameters_create']['selected'])) {
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

    public function indexAction()
    {
        $error = false;
        $response = [];
        $req = (array)Req::get();

        $type = (string)$req["type"];
        $parent_id = (string)$req["parent"];
        $category = (string)$req["category_id"];
        $description = (string)$req["description"];
        $price = (float)$req["price"];
        $weight = (array)$req["weight"];
        $work_type = (int)$req["work_type"];
        $currency_rate = (float)$req["currency_rate"];
        $currency_slug = (string)$req["currency_slug"];
        $currency_symbol = (string)$req["currency_symbol"];
        $gmt_offset = (string)$req["gmt_offset"];
        $has_daylight_saving = (int)$req["has_daylight_saving"];
        $summer_daylight_date = (string)$req["summer_daylight_date"];
        $winter_daylight_date = (string)$req["winter_daylight_date"];
        $timezone_slug = (string)$req["timezone_slug"];
        $active = (int)$req["active"];

        $added = false;
        $titles = [];
        foreach ((array)$req["titles"] as $lang => $value) {
            $lang = strtolower($lang);
            $name = trim($value);
            if (in_array($lang, Lang::$langs) && strlen($name) > 0) {
                $titles[$lang] = $name;
                $added = true;
            }
        }

        if (!$added) {
            $error = Lang::get("FieldsEmpty", "Fields are empty");
        } elseif (!Parameters::typeListByKey()[$type]) {
            $error = Lang::get("TypeIsWrong", "Type is wrong");
        } else {
            $new_id = Parameters::getNewId($type);
            $new_index = 1;

            $lastItem = Parameters::findFirst([
                [
                    "type" => (string)$type,
                    "parent_id" => (string)$parent_id ? $parent_id : 0,
                ],
                "sort" => [
                    "index" => -1,
                ],
            ]);
            if ($lastItem) {
                $new_index = $lastItem->index + 1;
            }

            $params = [
                "id" => (int)$new_id,
                "type" => (string)$type,
                "parent_id" => (string)$parent_id ? $parent_id : 0,
                "category" => (string)$category,
                "titles" => (array)$titles,
                "active" => (int)$active,
                "is_deleted" => 0,
                "index" => $new_index,
                "default_lang" => _LANG_,
                "slug" => str_replace(" ", "_", strtolower($titles["en"])),
                "created_at" => Parameters::getDate(),
            ];

            if (in_array($type, ["time_record_categories"])) {
                $params = array_merge($params, [
                    "work_type" => (int)$work_type,
                ]);
            }

            if (in_array($type, ["price_list"])) {
                $params = array_merge($params, [
                    "price" => (float)$price,
                    "weight" => (array)$weight,
                ]);
            }

            if (in_array($type, ["currencies"])) {
                $params = array_merge($params, [
                    "currency_rate" => (float)$currency_rate,
                    "currency_slug" => (string)$currency_slug,
                    "currency_symbol" => (string)$currency_symbol,
                ]);
            }

            if (in_array($type, ["timezones"])) {
                $params = array_merge($params, [
                    "gmt_offset" => (string)$gmt_offset,
                    "has_daylight_saving" => (int)$has_daylight_saving,
                    "timezone_slug" => (string)$timezone_slug,
                    "summer_daylight_date" => (string)$summer_daylight_date,
                    "winter_daylight_date" => (string)$winter_daylight_date,
                ]);
            }

            $insert_id = Parameters::insert($params);

            $response = array(
                "status" => "success",
                "description" => Lang::get("AddedSuccessfully", "Added successfully"),
            );

            // Log start
            Activities::log([
                "user_id" => (string)Auth::getData()->_id,
                "section" => "parameters",
                "operation" => "parameters_create",
                "values" => [
                    "id" => $insert_id,
                    "type" => $type,
                ],
                "status" => 1,
            ]);
            // Log end
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
