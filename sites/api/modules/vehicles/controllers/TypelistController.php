<?php

namespace Controllers;

use Custom\Models\Vehicles;

class TypelistController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $response = [
            'status' => 'success',
            'data' => Vehicles::typeList($this->lang)
        ];
        echo json_encode($response, true);
        exit();
    }
}