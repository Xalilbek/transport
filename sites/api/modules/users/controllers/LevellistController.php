<?php
namespace Controllers;

use Custom\Models\Users;
use Lib\Lang;

class LevellistController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $response = [
            'status' => 'success',
            'data'   => Users::levelList(Lang::getLang()),
        ];
        echo json_encode($response, true);
        exit();
    }
}
