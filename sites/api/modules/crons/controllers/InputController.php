<?php
namespace Controllers;

use Custom\Models\LogsRawTracking;
use Custom\Models\LogsUnknownTracking;
use Lib\Req;

class InputController extends \Phalcon\Mvc\Controller
{
	public function indexAction()
	{
		$json = urldecode(Req::get("json"));
		$obj = json_decode($json, TRUE);

		if(strlen($obj["ipAddress"]) > 0)
		{
            if($obj["network"] == "azercell"){
                $unixtime = strtotime($obj["timestamp"]) - 4*3600;
            }elseif($obj["protocol"] == "ruptela"){
                $unixtime = strtotime($obj["timestamp"]) - 3*3600;
            }else{
                $unixtime = strtotime($obj["timestamp"]) - 8 * 3600;
            }
			$imei = ($obj["imei"]) ? $obj["imei"]: $obj["deviceId"];
			$insert = [
				"data"			=> $obj,
				//"timestamp"		=> date("Y-m-d H:i:s", strtotime($obj["timestamp"])-4*3600),
                "business_id" => 0,
                "unixtime"		=> $unixtime,
                "timestamp" => date("Y-m-d H:i:s",$unixtime),
				"created_at" 	=> LogsRawTracking::getDate(),
			];
            LogsRawTracking::insert($insert);
            echo "Inserted to LogsRawTracking<br/>";
        }elseif(strlen($json) > 1){
		    echo "Inserted to LogsUnknownTracking<br/>";
		    echo $json."<br/>";
		    var_dump($obj);

			LogsUnknownTracking::insert([
				"json" => $json,
				"business_id" => 0,
				"created_at" 	=> LogsRawTracking::getDate(),
			]);
		}
		exit("okk");
	}


	public function statusAction()
	{
		$imei 	= trim(Req::get("imei"));
		$status = (int)(Req::get("status"));

		exit($imei."-".$status);
	}
}