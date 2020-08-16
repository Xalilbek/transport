<?php

namespace Controllers;

use Custom\Models\Alerts;
use Lib\Lang;

class AlerttypesController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $data = Alerts::getTypes(Lang::getLang());

        $response = [
            "status"	=> "success",
            "data"		=> $data
        ];
        echo json_encode($response, true);
        exit();
    }
}