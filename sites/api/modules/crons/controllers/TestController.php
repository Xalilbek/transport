<?php

namespace Controllers;

use Custom\Models\Alerts;
use Custom\Models\GeoObjects;
use Custom\Models\History;
use Custom\Models\LogsRawTracking;
use Custom\Models\LogsTracking;
use Custom\Models\LogsUnknownTracking;
use Custom\Models\Notifications;
use Custom\Models\Objects;

class TestController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $phpStart = microtime(true);
        for ($i = 0; $i < 50; $i++) {

            $trackings = LogsRawTracking::find([
                [

                    "data.imei" => 861359039145709

                ],
                "sort" => [
                    "unixtime" => 1
                ],
                "limit" => 1,

            ]);

            foreach ($trackings as $value) {
                if ($value->data) {
                    $obj = $value->data;
                    echo "ID: " . $value->_id . " - " . $obj->timestamp . "IMEI: ".$obj->imei."<br/>";

                    $timestamp = $value->unixtime;
                    $km = (float)explode(" ", trim($obj->speed))[0];
                    $imei = (string)$obj->imei;
                    $imeiData = Objects::findFirst([["imei" => $imei]]);
                    $geoJson = [
                        "type" => "Point",
                        "coordinates" => [
                            (float)$obj->longitude, (float)$obj->latitude
                        ]
                    ];

                    $durationElapse = 0;
                    $lastDuration = 0;
                    $action = "move";
                    if ($imeiData) {
                        if ($imeiData->last_history_id) {
                            $lastHisotry = History::findById($imeiData->last_history_id);
                        } else {
                            $lastHisotry = $this->getLastHistory($imeiData->_id, $timestamp, $imei, $geoJson);
                        }

                        list($lastLon, $lastLat) = Objects::getLonLatFromGeometry($imeiData->geometry);

                        $kmFrom = $this->calcDistance($lastLat, $lastLon, (float)$obj->latitude, (float)$obj->longitude);

                        if ($imeiData->connected_at)
                            $durationElapse = $timestamp - Objects::toSeconds($imeiData->connected_at);

                        if ($durationElapse > 300) {
                            $action = "parking";
                        } elseif ($durationElapse < 0) {
                            $durationElapse = 0;
                        } else {
                            $lastDuration = $durationElapse;
                        }

                        if ($action == "parking") {

                            echo " action changed<hr/>";

                            $historyDistance = LogsTracking::sum("last_distance", ["history_id" => (string)$lastHisotry->_id, "action" => "move"]);
                            //exit("sum ".$historyDistance);

                            History::update(
                                [
                                    "_id" => Objects::objectId($lastHisotry->_id)
                                ],
                                [
                                    "ended_at" => $imeiData->connected_at,
                                    "duration" => (Objects::toSeconds($imeiData->connected_at) - Objects::toSeconds($lastHisotry->started_at)),
                                    "distance" => $historyDistance,
                                    "geometry_to" => $geoJson,
                                ]
                            );

                            $lastHisotry = new History();
                            $lastHisotry->imei = $imei;
                            $lastHisotry->business_id = $imeiData->business_id;
                            $lastHisotry->object_id = (string)$imeiData->_id;
                            $lastHisotry->action = "parking";
                            $lastHisotry->started_at = $imeiData->connected_at;
                            $lastHisotry->ended_at = History::getDate($timestamp);
                            $lastHisotry->created_at = History::getDate();
                            $lastHisotry->geometry = $geoJson;
                            $lastHisotry->duration = abs($timestamp - History::toSeconds($imeiData->connected_at));
                            $lastHisotry->distance = 0;
                            $lastHisotry->save();

                            $lastHisotry = new History();
                            $lastHisotry->imei = $imei;
                            $lastHisotry->business_id = $imeiData->business_id;
                            $lastHisotry->object_id = (string)$imeiData->_id;
                            $lastHisotry->action = "move";
                            $lastHisotry->geometry_from = $geoJson;
                            $lastHisotry->started_at = History::getDate($timestamp);
                            $lastHisotry->created_at = History::getDate();
                            $lastHisotry->duration = 0;
                            $lastHisotry->distance = 0;
                            $lastHisotry->_id = $lastHisotry->save();

                        }
                        Objects::update(
                            [
                                "imei" => (string)$imei,
                            ],
                            [
                                "last_history_id" => (string)$lastHisotry->_id,
                                "geometry" => $geoJson,
                                "speed" => $km,
                                "angle" => (float)$obj->angle,
                                "updated_at" => Objects::getDate(),
                                "connected_at" => Objects::getDate($timestamp),
                            ]
                        );
                        $id = $imeiData->_id;


                        // ########################### start ALERTS ############################
                        $this->checkAlert($imeiData, $obj, $lastHisotry);

                        // ############################ end ALERTS #############################
                    } else {

                        $objects_obj = new Objects();
                        $objects_obj->id              = Objects::getNewId();
                        $objects_obj->business_id     = 0;
                        $objects_obj->imei            = $imei;
                        $objects_obj->geometry        = $geoJson;
                        $objects_obj->speed           = $km;
                        $objects_obj->angle           = (float)$obj->angle;
                        $objects_obj->is_deleted      = 0;
                        $objects_obj->connected_at    = Objects::getDate($timestamp);
                        $objects_obj->updated_at      = Objects::getDate();
                        $objects_obj->created_at      = Objects::getDate();
                        $objects_obj->save();
                        $objects_obj = Objects::getByImei($imei);
                        $id = $objects_obj->_id;
                        $lastHisotry = $this->getLastHistory($id, $timestamp, $imei, $geoJson);
                        $update = [
                            "last_history_id" => (string)$lastHisotry->_id
                        ];
                        Objects::update(["_id"	=> Objects::objectId($id)], $update);

                        $kmFrom = 0;
                    }

                    $T = new LogsTracking();
                    $T->object_id = (string) $id;
                    $T->business_id = $lastHisotry->business_id ? $lastHisotry->business_id : 0;
                    $T->imei = $imei;
                    $T->history_id = (string)$lastHisotry->_id;
                    $T->action = $action;
                    $T->geometry = $geoJson;
                    $T->angle = (float)$obj->angle;
                    $T->speed = $km;
                    $T->last_distance = $kmFrom;
                    $T->last_duration = $lastDuration;
                    $T->duration = $durationElapse;
                    $T->_test = [
                        "raw_ts" => Objects::getDate($timestamp),
                        "ts" => Objects::getDate(),
                        "created_at" => $value->created_at,
                    ];
                    $T->datetime = Objects::getDate($timestamp);
                    $logId = (string)$T->save();

                    LogsRawTracking::deleteRaw(["_id" => $value->_id]);
                } else {
                    LogsRawTracking::deleteRaw(["_id" => $value->_id]);

                    LogsUnknownTracking::insert([
                        "json" => $value->json,
                        "created_at" => LogsUnknownTracking::getDate(),
                    ]);
                }

                if (microtime(true) - $phpStart > 50)
                    exit;
            }

            sleep(1);
            if (microtime(true) - $phpStart > 50)
                exit;
        }

        exit;
    }


    public function checkAlert($obj, $trackData, $hisotry)
    {
        $coords = [(float)$trackData->longitude, (float)$trackData->latitude];
        $alerts = Alerts::getByObjectId($obj->_id);

        $geoJson = [
            "type" => "Point",
            "coordinates" => [
                (float)$trackData->longitude, (float)$trackData->latitude
            ]
        ];

        if ($alerts) {


            echo(count($alerts) . " alert found for " . $obj->title . "<br/>");

            foreach ($alerts as $alert) {
                $gezoneIds = [];
                foreach ($alert->geozone_ids as $gezone)
                {
                    $gezoneIds[] = GeoObjects::objectId($gezone);
                }

                $geopointIds = [];
                foreach ($alert->geopoint_ids as $geopoint)
                {
                    $geopointIds[] = GeoObjects::objectId($geopoint);
                }
                switch ((int)$alert->type) {
                    case 1:
                        $nowIn = (GeoObjects::checkPointInGeozones($gezoneIds, $coords)) ? true : false;

                        if (!$nowIn) {
                            $prevIn = (GeoObjects::checkPointInGeozones($gezoneIds, $obj->geometry->coordinates)) ? true : false;
                            if ($prevIn) {
                                $N = new Notifications();
                                $N->users = (string)$obj->users;
                                $N->object_id = (string)$obj->_id;
                                $N->business_id = $obj->business_id;
                                $N->alert_id = (string)$alert->_id;
                                $N->alert_type = (int)$alert->type;
                                $N->history_id = (string)$hisotry->_id;
                                $N->geometry = $geoJson;
                                $N->text = (string)$alert->text;
                                $N->datetime = Notifications::getDate($trackData->unixtime);
                                $N->created_at = Notifications::getDate();
                                $N->save();

                                echo "<font style='color: red;'>ALERT - GEOZONE OUT ###########################</font><br/>";
                            }
                        }

                        break;

                    case 2:
                        $nowIn = (GeoObjects::checkPointInGeozones($gezoneIds, $coords)) ? true : false;

                        if ($nowIn) {
                            $prevIn = (GeoObjects::checkPointInGeozones($gezoneIds, $obj->geometry->coordinates)) ? true : false;


                            if (!$prevIn) {
                                $N = new Notifications();
                                $N->users = (string)$obj->users;
                                $N->business_id = $obj->business_id;
                                $N->object_id = (string)$obj->_id;
                                $N->alert_id = (string)$alert->_id;
                                $N->alert_type = (int)$alert->type;
                                $N->geometry = $geoJson;
                                $N->history_id = (string)$hisotry->_id;
                                $N->text = (string)$alert->text;
                                $N->datetime = Notifications::getDate($trackData->unixtime);
                                $N->created_at = Notifications::getDate();
                                $N->save();

                                echo "<font style='color: red;'>ALERT - GEOZONE IN ###########################</font><br/>";
                            }
                        }

                        break;

                    case 3:
                        $points = GeoObjects::getPointsByIds($geopointIds);

                        if (count($points) > 0) {
                            foreach ($points as $point) {
                                $nowDis = $this->calcDistance((float)$trackData->longitude, (float)$trackData->latitude, (float)$point->geometry->coordinates[0], (float)$point->geometry->coordinates[1]);

                                if ($alert->radius <= $nowDis) {
                                    $prevDis = $this->calcDistance((float)$point->geometry->coordinates[0], (float)$point->geometry->coordinates[1], (float)$obj->geometry->coordinates[0], (float)$obj->geometry->coordinates[1]);

                                    if ($alert->radius > $prevDis) {
                                        $N = new Notifications();
                                        $N->users = (string)$obj->users;
                                        $N->object_id = (string)$obj->_id;
                                        $N->business_id = $obj->business_id;
                                        $N->alert_id = (string)$alert->_id;
                                        $N->alert_type = (int)$alert->type;
                                        $N->history_id = (string)$hisotry->_id;
                                        $N->geometry = $geoJson;
                                        $N->text = (string)$alert->text;
                                        $N->datetime = Notifications::getDate($trackData->unixtime);
                                        $N->created_at = Notifications::getDate();
                                        $N->save();

                                        echo "<font style='color: red;'>ALERT - AWAY FROM POINT ###########################</font><br/>";
                                    }
                                }
                            }
                        }
                        break;

                    case 4:
                        $points = GeoObjects::getPointsByIds($geopointIds);
                        if (count($points) > 0) {
                            foreach ($points as $point) {
                                $nowDis = $this->calcDistance((float)$trackData->longitude, (float)$trackData->latitude, (float)$point->geometry->coordinates[0], (float)$point->geometry->coordinates[1]);
                                if ($alert->radius >= $nowDis) {
                                    $prevDis = $this->calcDistance( (float)$point->geometry->coordinates[0], (float)$point->geometry->coordinates[1], (float)$obj->geometry->coordinates[0], (float)$obj->geometry->coordinates[1]);
                                    if ($alert->radius < $prevDis) {
                                        $N = new Notifications();
                                        $N->users = (string)$obj->users;
                                        $N->object_id = (string)$obj->_id;
                                        $N->business_id = $obj->business_id;
                                        $N->alert_id = (string)$alert->_id;
                                        $N->alert_type = (int)$alert->type;
                                        $N->history_id = (string)$hisotry->_id;
                                        $N->geometry = $geoJson;
                                        $N->text = (string)$alert->text;
                                        $N->datetime = Notifications::getDate($trackData->unixtime);
                                        $N->created_at = Notifications::getDate();
                                        $N->save();

                                        echo "<font style='color: red;'>ALERT - NEAR TO POINT ###########################</font><br/>";
                                    }
                                }
                            }
                        }
                        break;

                    case 5:
                        if ($obj->speed < $alert->speed && $trackData->speed >= $alert->speed) {
                            $N = new Notifications();
                            $N->users = (string)$obj->users;
                            $N->object_id = (string)$obj->_id;
                            $N->business_id = $obj->business_id;
                            $N->alert_id = (string)$alert->_id;
                            $N->alert_type = (int)$alert->type;
                            $N->speed = (int)$trackData->speed;
                            $N->history_id = (string)$hisotry->_id;
                            $N->geometry = $geoJson;
                            $N->text = (string)$alert->text;
                            $N->datetime = Notifications::getDate($trackData->unixtime);
                            $N->created_at = Notifications::getDate();
                            $N->save();

                            echo "<font style='color: red;'> ALERT - OVER SPEED ###########################</font><br/>";
                        }
                        break;
                }
            }
        } else {

        }
    }


    public function calcDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
                cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }


    public function getLastHistory($objId, $timestamp, $imei, $geoJson)
    {
        $data = History::findFirst([
            [
                "object_id" =>$objId,
                "imei" => $imei
            ],
            "sort" => [
                "_id" => -1,
            ],
            "limit" => 1,
        ]);
        if (!$data) {
            History::insert(
                [
                    "business_id" => Objects::findFirst(['_id'=>$objId])->business_id,
                    "object_id" =>(string)$objId,
                    "imei" => $imei,
                    "action" => "move",
                    "geometry_from" => $geoJson,
                    "started_at" => History::getDate($timestamp),
                    "created_at" => History::getDate()
                ]);

        }


        return $data;
    }


    public function disAction()
    {
        $historyDistance = LogsTracking::sum("duration", ["history_id" => "5c2abd7187d2db74e3683fa0"]);
        var_dump($historyDistance);
        exit;
    }
}