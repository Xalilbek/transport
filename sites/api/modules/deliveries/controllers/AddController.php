<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Deliveries;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class AddController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $employee_id = (string) trim($req['employee_id']);
        $number      = (string) trim($req['number']);
        $weight      = (float) trim($req['weight']);
        $price       = (float) trim($req['price']);
        $address     = (string) trim($req['address']);
        $geometry    = (array) trim($req['geometry']);

        $key = md5($number . $address);

        if (Cache::is_brute_force("deliveryAdd-" . $key, [
            "minute" => 20,
            "hour"   => 50,
            "day"    => 100,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("deliveryAdd-" . Req::getServer("REMOTE_ADDR"), [
            "minute" => 40,
            "hour"   => 300,
            "day"    => 900,
        ])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } else {
            if ($permissions['deliveries_create']['allow']) {
                $new_id = Deliveries::getNewId();
                $insert = [
                    "id"          => (int) $new_id,
                    "creator_id"  => (string) Auth::getData()->_id,
                    "employee_id" => (string) $employee_id,
                    "number"      => (string) $number,
                    "weight"      => (float) $weight,
                    "price"       => (float) $price,
                    "address"     => (string) substr($address, 0, 1000),
                    "geometry"    => (array) $geometry,
                    "status"      => 0,
                    "is_deleted"  => 0,
                    "created_at"  => Deliveries::getDate(),
                ];

                $insert_id = Deliveries::insert($insert);

                $response = array(
                    "status"      => "success",
                    "description" => Lang::get("AddedSuccessfully", "Added successfully"),
                );

                // Log start
                Activities::log([
                    "user_id"   => (string)Auth::getData()->_id,
                    "section"   => "delivery",
                    "operation" => "delivery_create",
                    "values"    => [
                        "id" => $insert_id,
                    ],
                    "status"    => 1,
                ]);
                // Log end
            } else {
                $error = Lang::get("PageNotAllowed");
            }
        }
        if ($error) {
            $response = [
                "status"      => "error",
                "error_code"  => 1017,
                "description" => $error,
            ];
        }
        echo json_encode((object) $response);
        exit;
    }
}
