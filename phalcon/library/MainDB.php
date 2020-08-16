<?php
namespace Lib;

use Custom\Models\Objects;
use Models\Users;

class MainDB extends \Phalcon\Mvc\Controller
{
    public static $connection = false;

    public static $db = MONGO_DB;

    public static $collection = false;

    public static function fromJSON($json)
    {
        return \MongoDB\BSON\fromJSON($json);
    }

    public static function toJSON($bson)
    {
        return \MongoDB\BSON\toJSON($bson);
    }

    public static function fromPHP($bson)
    {
        return \MongoDB\BSON\fromPHP($bson);
    }

    public static function toPHP($bson)
    {
        return \MongoDB\BSON\toPHP($bson);
    }

    public static function getSource()
    {
        return null;
    }

    public static function setCollection($collection)
    {
        self::$collection = $collection;
    }

    public static function init()
    {
        self::$collection = static::getSource();
        if (!self::$connection) {
            self::$connection = new \MongoDB\Driver\Manager('mongodb://localhost:27017/' . self::$db);
        }

    }

    public static function filterBinds($data)
    {
        if (in_array(self::$collection, ["translations", "crm_types", "user_tokens", "businesses", "parameters"])) {
            return $data;
        }


        if (!isset($data['crm_type']) && $data['business_id'] !== 0) {
            //$data["crm_type"] = CRM_TYPE;
        }
        if (!isset($data['business_id']) && $data['business_id'] !== 0) {
            $data["business_id"] = BUSINESS_ID;
        }
        return $data;
    }

    public static function listById($data, $column, $callback)
    {
        $ids  = [];
        $list = [];

        foreach ($data as $row) {
            if (!in_array($row->{$column}, $ids)) {
                $ids[] = $row->{$column};
            }
        }


        $res = $callback($ids);
        foreach ($res["rows"] as $row) {
            if (is_object($row->{$res["col"]})) {
                $list[(string) $row->{$res["col"]}] = $row;
            } else {
                $list[$row->{$res["col"]}] = $row;
            }
        }

        return $list;
    }

    public static function insert($data)
    {
        self::init();
        $data = self::filterBinds((array) $data);
        if(is_array($data['is_deleted']) && array_key_exists('$ne', $data['is_deleted'])){
            $data['is_deleted'] = ((int)$data['is_deleted']['$ne'] == 1 ? 0 : 1);
        }

        //exit(json_encode($data));

        $insRec = new \MongoDB\Driver\BulkWrite;
        $id     = $insRec->insert($data);
        $result = self::$connection->executeBulkWrite(self::$db . '.' . self::$collection, $insRec);

        if ($result) {
            return $id;
        } else {
            return false;
        }
    }

    public function save()
    {
        $ins = [];
        foreach ($this as $key => $value) {
            if ($key !== "_id") {
                $ins[$key] = $value;
            }

        }

        $ins = self::filterBinds((array) $ins);
        if(is_array($ins['is_deleted']) && array_key_exists('$ne', $ins['is_deleted'])){
            $ins['is_deleted'] = ((int)$ins['is_deleted']['$ne'] == 1 ? 0 : 1);
        }

        if ($this->_id) {
            return self::update(["_id" => $this->_id], $ins);
        }

        return self::insert($ins);
    }

    public static function count($array = [])
    {
        $filter  = (@$array[0]) ? $array[0] : [];
        $options = [];
        self::init();
        $filter = self::filterBinds((array) $filter);

        $Command = new \MongoDB\Driver\Command(["count" => self::$collection, "query" => $filter]);
        $Result  = self::$connection->executeCommand(self::$db, $Command);
        return $Result->toArray()[0]->n;
    }

    public static function sum($field, $filter = [])
    {
        self::init();

        $pipleLine = [];
        $filter    = self::filterBinds((array) $filter);
        if (count($filter) > 0) {
            $pipleLine[] = ['$match' => $filter];
        }

        $pipleLine[] = [
            '$group' => ['_id' => '$asdak', 'total' => ['$sum' => '$' . $field], 'count' => ['$sum' => 1]],
        ];
        $Command = new \MongoDB\Driver\Command([
            'aggregate' => self::$collection,
            'pipeline'  => $pipleLine,
            "cursor" => [ "batchSize" => 1 ]
        ]);

        $Result = self::$connection->executeCommand(self::$db, $Command);

        //echo var_dump($field);
        //echo "<pre>";var_dump($Result->toArray()[0]->result[0]);exit;


//        if(@$Result->toArray()[0]->result)
//            return $Result->toArray()[0]->result[0];
        return $Result->toArray()[0]->total;
    }

    public static function find($array = [])
    {
        $filter  = (@$array[0]) ? $array[0] : [];
        $options = [];
        if (isset($array["limit"])) {
            $options["limit"] = @$array["limit"];
        }

        if (isset($array["sort"])) {
            $options["sort"] = @$array["sort"];
        }

        if (isset($array["skip"])) {
            $options["skip"] = $array["skip"];
        }

        self::init();
        $filter = self::filterBinds((array) $filter);
        $query = new \MongoDB\Driver\Query($filter, $options);
        $rows  = self::$connection->executeQuery(self::$db . '.' . self::$collection, $query);

        return $rows->toArray();
    }

    public static function findById($id)
    {
        $filter["_id"] = self::objectId($id);
        self::init();
        $filter = self::filterBinds((array) $filter);
        $query  = new \MongoDB\Driver\Query($filter, []);
        $rows   = self::$connection->executeQuery(self::$db . '.' . self::$collection, $query);
        foreach ($rows as $row) {
            $obj = new static();
            foreach ($row as $k => $v) {
                $obj->{$k} = $v;
            }
            return $obj;
        }
        return false;
    }

    public static function findFirst($array = [])
    {
        $filter           = (@$array[0]) ? $array[0] : [];
        $options          = [];
        $options["limit"] = 1;
        if (isset($array["sort"])) {
            $options["sort"] = @$array["sort"];
        }

        if (isset($array["skip"])) {
            $options["skip"] = $array["skip"];
        }

        self::init();
        $filter = self::filterBinds((array) $filter);
        $query  = new \MongoDB\Driver\Query($filter, $options);
        $rows   = self::$connection->executeQuery(self::$db . '.' . self::$collection, $query);
        foreach ($rows as $row) {
            $obj = new static();
            foreach ($row as $k => $v) {
                $obj->{$k} = $v;
            }
            return $obj;
        }
        return false;
    }

    public static function update($filter, $data)
    {
        self::init();
        $filter  = self::filterBinds((array) $filter);

        if(is_array($data['is_deleted']) && array_key_exists('$ne', $data['is_deleted'])){
            $data['is_deleted'] = ((int)$data['is_deleted']['$ne'] == 1 ? 0 : 1);
        }

        $options = ['multi' => true, 'upsert' => false];
        $insRec  = new \MongoDB\Driver\BulkWrite;
        $insRec->update(
            $filter,
            ['$set' => $data],
            $options
        );
        $result = self::$connection->executeBulkWrite(self::$db . '.' . self::$collection, $insRec);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function increment($filter, $data)
    {
        self::init();
        $filter  = self::filterBinds((array) $filter);
        $options = ['multi' => true, 'upsert' => false];
        $insRec  = new \MongoDB\Driver\BulkWrite;
        $insRec->update(
            $filter,
            ['$inc' => $data],
            $options
        );
        $result = self::$connection->executeBulkWrite(self::$db . '.' . self::$collection, $insRec);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function updateAndIncrement($filter, $update, $increment)
    {
        self::init();
        $filter  = self::filterBinds((array) $filter);
        if(is_array($increment['is_deleted']) && array_key_exists('$ne', $increment['is_deleted'])){
            $increment['is_deleted'] = ((int)$increment['is_deleted']['$ne'] == 1 ? 0 : 1);
        }

        $options = ['multi' => true, 'upsert' => false];
        $insRec  = new \MongoDB\Driver\BulkWrite;
        $insRec->update(
            $filter,
            [
                '$set' => $update,
                '$inc' => $increment,
            ],
            $options
        );
        $result = self::$connection->executeBulkWrite(self::$db . '.' . self::$collection, $insRec);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteRaw($filter)
    {
        self::init();
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->delete($filter, ['limit' => 0]);
        $result = self::$connection->executeBulkWrite(self::$db . '.' . self::$collection, $bulk);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function delete()
    {
        if ($this->_id) {
            return self::deleteRaw(["_id" => $this->_id]);
        }

        return false;
    }

    public static function getDate($time = false)
    {
        if (!$time) {
            $time = time();
        }

        //$time -= TIME_DIFF;
        $time *= 1000;
        $datetime = new \MongoDB\BSON\UTCDateTime($time);
        return $datetime;
    }

    public static function dateTime($date)
    {
        return self::dateFormat($date, $format = "Y-m-d H:i:s");
    }

    public static function dateFormat($date, $format = "Y-m-d H:i:s")
    {
        return date($format, self::toSeconds($date));
    }

    public static function toSeconds($date)
    {
        if ($date && method_exists($date, "toDateTime")) {
            return round(@$date->toDateTime()->format("U.u"), 0);
        }

        return 0;
    }

    public static function secondsToTime( $inputSeconds) {
        $seconds = self::getTime() - $inputSeconds;

        if($seconds/60 < 60){
            $minutes = (int)($seconds/60);
        }elseif($seconds/3600 < 24){
            $hours = (int)($seconds/3600);
        }elseif($seconds/86400 < 35){
            $days = (int)($seconds/86400);
        }elseif($seconds/(30*86400) < 13){
            $months = (int)($seconds/86400/30);
        }

        if($seconds < 120){
            $date_text = Lang::get("Online");
        }elseif($minutes > 0 && $minutes < 61){
            $date_text = $minutes." ".Lang::get("minutes", "minutes")." ".Lang::get("ago");
        }elseif($hours > 0 && $hours < 25){
            $date_text = $hours." ".Lang::get("hours", "hours")." ".Lang::get("ago");
        }elseif($days > 0 && $days < 34){
            $date_text = $days." ".Lang::get("days", "days")." ".Lang::get("ago");
        }elseif($months > 0 && $months < 12){
            $date_text = $months." ".Lang::get("months", "month(s)")." ".Lang::get("ago");
        }else{
            $date_text = date("Y-m-d H:i:s", $inputSeconds);
        }
        return trim($date_text);
    }

    public static function getTime()
    {
        return time() + TIME_DIFF;
    }
    public static function objectId($id)
    {
        if (self::isMongoId($id)) {
            return new \MongoDB\BSON\ObjectID($id);
        }
        return false;
    }

    public static function isMongoId($id)
    {
        if ($id instanceof \MongoDB\BSON\ObjectID || preg_match('/^[a-f\d]{24}$/i', $id)) {
            return true;
        }

        try {
            new \MongoDB\BSON\ObjectID($id);
            return true;
        } catch (\Exception $e) {
            return false;
        } catch (\MongoException $e) {
            return false;
        }
    }
    public static function dateFiltered($date, $format = "Y-m-d H:i:s")
    {
        if($date && method_exists($date, "toDateTime"))
            return date($format, self::toSeconds($date));
        return 0;
    }

    public function dateConvertTimeZone($date, $format = "Y-m-d H:i:s"){
        if($date && method_exists($date, "toDateTime")){
//            var_dump(self::toSeconds($date) + CLIENT_DATE_DIFFERENCE);
//            var_dump(CLIENT_DATE_DIFFERENCE);
//            var_dump(date($format,self::toSeconds($date) + CLIENT_DATE_DIFFERENCE));
//            exit();
            return date($format,self::toSeconds($date) + CLIENT_DATE_DIFFERENCE);
        }
        return 0;

    }
    public function updateUserId($class, $keys)
    {
        try {


            $list = $class::find([
                [

                ],
                "sort" => [
                    "_id" => 1
                ]
            ]);

            foreach ($list as $value) {

                foreach ($keys as $key){

                    if ($value->$key && Users::getById((int)$value->$key)->id) {

                        if ($mongoid = (string)Users::getById((int)$value->$key)->_id) {

                            $class::update(
                                [$key => $value->$key],
                                [
                                    $key => (string)Users::getById($value->$key)->_id,
                                ]
                            );

                        }
                    }
                }

            }

            return $class." updated";

        } catch (\Exception $exception) {
            return $exception;
        }

    }
}
