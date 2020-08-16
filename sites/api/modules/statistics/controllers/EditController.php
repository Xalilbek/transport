<?php

namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Deliveries;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class EditController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req = (array)Req::get();
        $id = (string)$req['id'];
        $employee_id = (int)trim($req['employee_id']);
        $number = (string)trim($req['number']);
        $weight = (float)trim($req['weight']);
        $price = (float)trim($req['price']);
        $address = (string)trim($req['address']);
        $geometry = (array)trim($req['geometry']);

        $data = Deliveries::findFirst([
            [
                "_id" => Deliveries::objectId($id),
                "is_deleted" => ['$ne' => 1],
            ],
        ]);
        if (Cache::is_brute_force("deliveryEdit-" . $id, ["minute" => 100, "hour" => 1000, "day" => 3000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (!$data) {
            $error = Lang::get("noInformation", "No information found");
        } else {
            if ($permissions['deliveries_update']['allow']) {
                $update = [
                    "employee_id" => (int)$employee_id,
                    "number" => (string)$number,
                    "weight" => (float)$weight,
                    "price" => (float)$price,
                    "address" => (string)substr($address, 0, 1000),
                    "geometry" => (array)$geometry,
                    "updated_at" => Deliveries::getDate(),
                ];

                Deliveries::update(["_id" => $data->_id], $update);
                $response = [
                    "status" => "success",
                    "description" => Lang::get("UpdatedSuccessfully", "Updated successfully"),
                ];

                // Log start
                Activities::log([
                    "user_id" => (string)Auth::getData()->_id,
                    "section" => "delivery",
                    "operation" => "delivery_update",
                    "values" => [
                        "id" => $data->_id,
                    ],
                    "oldObject" => $data,
                    "newObject" => Deliveries::findById($data->_id),
                    "status" => 1,
                ]);
                // Log end
            } else {
                $error = Lang::get("PageNotAllowed");
            }
        }
        if ($error) {
            $response = [
                "status" => "error",
                "error_code" => 1017,
                "description" => $error,
            ];
        }
        echo json_encode((object)$response);
        exit;
    }
}
