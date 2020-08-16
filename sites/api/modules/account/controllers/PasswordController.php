<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;

class PasswordController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $error = false;

        $oldpassword = trim(Req::get("oldpassword"));
        $password    = trim(Req::get("password"));
        $repassword  = trim(Req::get("repassword"));

        if (Cache::is_brute_force("infocUp-" . Req::getServer("REMOTE_ADDR"), ["minute" => 100, "hour" => 500, "day" => 9000])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Lib::generatePassword($oldpassword) !== Auth::getData()->password) {
            $error = Lang::get("OldPasswordError", "Old password is wrong");
        } elseif (strlen($password) < 6 || strlen($password) > 100) {
            $error = Lang::get("PasswordError", "Password is wrong (min 6 characters)");
        } elseif (strlen($repassword) > 0 && $password !== $repassword) {
            $error = Lang::get("RePasswordError", "Passwords dont match");
        } else {
            $update = [
                "password" => Lib::generatePassword($password),
            ];

            Users::update(["_id" => Auth::getData()->_id], $update);

            $success = Lang::get("UpdatedSuccessfully");

            $response = [
                "status"      => "success",
                "description" => $success,
            ];
        }

        if ($error) {
            $response = [
                "status"      => "error",
                "description" => $error,
                "error_code"  => 1021,
            ];
        }

        echo json_encode($response, true);
        exit();
    }
}
