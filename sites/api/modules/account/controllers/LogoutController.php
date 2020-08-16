<?php
namespace Controllers;

use Custom\Models\Tokens;
use Lib\Auth;
use Lib\Lang;

class LogoutController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $error = false;
        $token = Auth::getToken();
        if ($token) {
            Tokens::update(
                [
                    "token" => $token,
                ],
                [
                    "active"      => 0,
                    "logouted_at" => Tokens::getDate(),
                ]
            );
        }

        $response = [
            "status"      => "success",
            "description" => Lang::get("ExecutedSuccessfully", "Executed Successfully"),
        ];

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
