<?php
namespace Controllers;

use Custom\Models\Users;
use Lib\Lang;

class TypelistController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $response = [
            'status' => 'success',
            'data'   => Users::typeList(Lang::getLang()),
        ];
        echo json_encode($response, true);
        exit();
    }
}
