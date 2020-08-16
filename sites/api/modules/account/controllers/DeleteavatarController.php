<?php
namespace Controllers;

use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;

class DeleteavatarController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $update = [
            "avatar" => false,
        ];

        Users::update(["_id" => Auth::getData()->_id], $update);

        $success = Lang::get("DeletedSuccessfully");

        $response = [
            "status"      => "success",
            "description" => $success,
        ];

        echo json_encode($response, true);
        exit();
    }
}
