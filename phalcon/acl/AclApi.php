<?php

use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use Models\LogsAccess;
use \Phalcon\Events\Event;
use \Phalcon\Mvc\Dispatcher;
use Lib\TimeZones;

class AclApi extends \Phalcon\Di\Injectable
{
    protected $_module;

    public function __construct($module)
    {
        $this->_module = $module;
    }

    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        header("Access-Control-Allow-Origin: *");
        $controller = $dispatcher->getControllerName();
        $module = $dispatcher->getModuleName();

        $timezoneData = TimeZones::getByCountryCode(@$_SERVER["HTTP_CF_IPCOUNTRY"]);
        if(!$timezoneData)
            $timezoneData=TimeZones::getByCountryCode('dk');
        define("DEFAULT_DATE_FORMAT", "Y-m-d H:i");
        define("DEFAULT_TIMEZONE", 100);
        define("USER_TIMEZONE", $timezoneData['id']);

        Req::init();
        Lang::init(2);
        Auth::init();

        if (!in_array($module, ["crons"])) {
            if (!in_array($controller, ["signin", "register", "data", "api"])) {
                if (!in_array($controller, ["settings"]) && Auth::$errorCode > 0) {
                    $response = [
                        "status"      => "error",
                        "error_code"  => Auth::$errorCode,
                        "description" => Auth::$error,
                    ];
                    exit(json_encode($response, true));
                }
            }
        }

        Lang::$data = Auth::$translations;

        $vars = $_REQUEST;
        unset($vars["_url"]);
        unset($vars["password"]);
        unset($vars["newpassword"]);
        unset($vars["oldpassword"]);

        $dateDifference =  strtotime(date(urldecode(Req::get('ts')))) - time();
        define("CLIENT_DATE_DIFFERENCE",   @$dateDifference);

        $insert = [
            "user_id"    => (Auth::getData()) ? (int) Auth::getData()->id : 0,
            "url"        => "https://" . $_SERVER["HTTP_HOST"] . "" . @$_SERVER["REQUEST_URI"],
            "ip"         => @$_SERVER["REMOTE_ADDR"],
            "browser"    => @$_SERVER["HTTP_USER_AGENT"],
            "variables"  => strlen(json_encode($vars, true)) > 1000 ? substr(json_encode($vars, true), 0, 1000) : $vars,
            "created_at" => LogsAccess::getDate(),
        ];
        $allowLogging = true;
        if ($allowLogging) {
            LogsAccess::insert($insert);
        }

    }
}
