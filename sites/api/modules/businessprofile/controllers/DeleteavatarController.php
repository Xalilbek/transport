<?php
namespace Controllers;

use Custom\Models\Businesses;
use Lib\Auth;
use Lib\Lang;

class DeleteavatarController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $update = [
            "avatar" => false,
        ];

        Businesses::update(["id" => (int) Auth::$business->data->id], $update);

        $success = Lang::get("DeletedSuccessfully");

        $response = [
            "status"      => "success",
            "description" => $success,
        ];

        echo json_encode($response, true);
        exit();
    }
}
