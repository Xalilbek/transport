<?php

namespace Controllers;

use Custom\Models\Transactions;

class TypelistController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $response = [
            'status' => 'success',
            'data' => Transactions::typeList($this->lang),
        ];
        echo json_encode($response, true);
        exit();
    }
}
