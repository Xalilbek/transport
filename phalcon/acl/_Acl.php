<?php
use \Phalcon\Events\Event;
use \Phalcon\Mvc\Dispatcher;

use Models\Auth;
use Models\LogsAccess;

class Acl extends \Phalcon\Mvc\User\Component
{
	protected $_module;

	public function __construct($module)
	{
		$this->_module = $module;
	}

	public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
	{
	    exit;
        //echo $resource = $this->_module . '-' . $dispatcher->getControllerName(), PHP_EOL; // frontend-dashboard
		//echo $access = $dispatcher->getActionName();

		if(!in_array($dispatcher->getControllerName(), ["auth"]))
		{
			//Auth::init($this->req, Lang::getLang());

			//if(Auth::error) exit(json_encode(["status" => "error", "error_code" => Auth::errorCode, "description" => Auth::error], true));

			$vars               = $_REQUEST;
			unset($vars["_url"]);

			$insert = [
				//"user_id"       => (int)@Users::$data->id,
				"url"           => @$_SERVER["REQUEST_URI"],
				"ip"            => @$_SERVER["REMOTE_ADDR"],
				"browser"       => @$_SERVER["HTTP_USER_AGENT"],
				"variables"     => strlen(json_encode($vars, true)) > 1000 ? substr(json_encode($vars, true),0,1000): $vars,
				"created_at"    => LogsAccess::getDate(),
			];
			LogsAccess::insert($insert);
		}


		$this->view->setVar("controller", $dispatcher->getControllerName());
		$this->view->setVar("action", $dispatcher->getActionName());
	}
}