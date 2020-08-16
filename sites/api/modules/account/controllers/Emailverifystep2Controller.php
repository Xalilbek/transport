<?php
namespace Controllers;

use Custom\Models\ForgetLogs;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Models\ForgetLogs;

class Emailverifystep2Controller extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $email = trim(strtolower(Req::get("email")));
        $code  = trim(Req::get("code"));

        $temp = ForgetLogs::findFirst([
            [
                "email"  => $email,
                "status" => 1,
            ],
            "sort" => [
                "_id" => -1,
            ],
        ]);

        if (!$temp) {
            $error = Lang::get("VerificaitonCodeWrong", "Verification code is wrong");
        } elseif ($temp->check_limit > 8) {
            $error = Lang::get("VerificaitonExpired", "Verification code has been expired");
        } else {
            $temp->check_limit += 1;

            $data = Auth::getData();
            if ((string) $temp->code == (string) $code) {

                ForgetLogs::deleteRaw([
                    "_id" => $temp->_id
                ]);

                $data->email             = $email;
                $data->email_verified    = 1;
                $data->email_verified_at = Users::getDate();
                $data->save();

                $response = array(
                    "status"      => "success",
                    "description" => Lang::get("EmailVerifed", "Email address was verified successfuly"),
                );
            } else {
                $error = Lang::get("VerificaitonCodeWrong", "Verification code is wrong");
                $temp->save();
            }
        }

        if ($error) {
            $response = array(
                "status"      => "error",
                "error_code"  => 1401,
                "description" => $error,
            );
        }
        echo json_encode($response, true);
        exit();
    }
}
