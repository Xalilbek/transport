<?php

namespace Controllers;

use Custom\Models\Parameters;
use Lib\Lang;

class TypelistController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $response = [
            'status' => 'success',
            'data' => Parameters::typeList()
        ];
        echo json_encode($response, true);
        exit();
    }
}