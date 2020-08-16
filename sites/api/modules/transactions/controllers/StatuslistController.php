<?php

namespace Controllers;

use Custom\Models\Transactions;
use Lib\Lang;

class StatuslistController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $response = [
            'status' => 'success',
            'data' => Transactions::statusList(Lang::getLang()),
        ];
        echo json_encode($response, true);
        exit();
    }
}
