<?php
namespace Lib;

use Lib\Lib;

class ApiDB extends \Phalcon\Mvc\Controller
{
    public static $url = 'http://crm.besfly.com/db';

    public static $connection = false;

    public static $db = MONGO_DB;

    public static $collection = false;

    public static function listById($data, $column, $callback)
    {
        $ids  = [];
        $list = [];

        foreach ((array) $data as $row) {
            $col = (array) $row;
            $keys = explode('.', $column);
            foreach ($keys as $key) {
                $arr = (array)$col[$key];
                $col = &$arr;
            }

            if (!in_array($col[0], $ids)) {
                $ids[] = $col[0];
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

    public static function request($action, $params = [])
    {
        $params["collection"] = self::$collection;

        $ch = curl_init(self::$url . '/' . $action);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            "_env" => _ENV,
            "token" => TOKEN,
            "server_token" => SERVER_TOKEN,
            "query" => bin2hex(self::fromPHP($params)),
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($ch);
        if ($result) {
            return json_decode($result);
        }
        return false;
    }

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

    public static function init()
    {
        self::$collection = static::getSource();
    }

    public function save()
    {
        $ins = [];
        foreach ($this as $key => $value) {
            if ($key !== "_id") {
                $ins[$key] = $value;
            }
        }

        if ($this->_id) {
            return self::update(["_id" => $this->_id], $ins);
        }

        return self::insert($ins);
    }

    public static function insert($data)
    {
        self::init();
        $request = self::request("insert", [
            "data" => $data,
        ]);
        //var_dump($request);exit;
        if ($request->status == "success" && $request->data) {
            return $request->data;
        } else {
            return false;
        }
    }

    public static function find($filter)
    {
        self::init();
        $request = self::request("find", [
            "filter" => $filter,
        ]);
        if ($request->status == "success" && $request->data) {
            return self::toPHP(hex2bin($request->data));
        } else {
            return false;
        }
    }

    public static function findFirst($filter)
    {
        self::init();
        $request = self::request("find/first", [
            "filter" => $filter,
        ]);

        if ($request->status == "success" && $request->data) {
            $data = self::toPHP(hex2bin($request->data));

            $obj = new static();
            foreach($data as $k => $v){
                $obj->{$k} = $v;
            }
            return $obj;
        } else {
            return false;
        }
    }

    public static function findById($id)
    {
        self::init();
        $request = self::request("find/id", [
            "filter" => [
                "_id" => self::objectId($id),
            ],
        ]);
        if ($request->status == "success" && $request->data) {
            return self::toPHP(hex2bin($request->data));
        } else {
            return false;
        }
    }

    public static function count($filter)
    {
        self::init();
        $request = self::request("count", [
            "filter" => $filter,
        ]);        
        if ($request->status == "success") {
            return (int) $request->data;
        } else {
            return 0;
        }
    }

    public static function update($filter, $data)
    {
        self::init();
        $request = self::request("update", [
            "filter" => $filter,
            "data"   => $data,
        ]);
        if ($request->status == "success" && $request->data) {
            return self::toPHP(hex2bin($request->data));
        } else {
            return false;
        }
    }

    public static function sum($field, $filter)
    {
        self::init();
        $request = self::request("sum", [
            "filter" => $filter,
            "field"  => $field,
        ]);
        if ($request->status == "success" && $request->data) {
            return self::toPHP(hex2bin($request->data));
        } else {
            return false;
        }
    }

    public static function increment($filter, $data)
    {
        self::init();
        $request = self::request("increment", [
            "filter" => $filter,
            "data"   => $data,
        ]);
        if ($request->status == "success" && $request->data) {
            return self::toPHP(hex2bin($request->data));
        } else {
            return false;
        }
    }

    public static function updateAndIncrement($filter, $update, $increment)
    {
        self::init();
        $request = self::request("updateAndIncrement", [
            "filter"    => $filter,
            "update"    => $update,
            "increment" => $increment,
        ]);
        if ($request->status == "success") {
            return self::toPHP(hex2bin($request->data));
        } else {
            return false;
        }
    }

    public static function delete($filter)
    {
        self::init();
        $request = self::request("delete", [
            "filter" => $filter,
        ]);
        if ($request->status == "success" && $request->data) {
            return self::toPHP(hex2bin($request->data));
        } else {
            return false;
        }

    }

    public static function getDate($time = false)
    {
        if (!$time) {
            $time = time();
        }
        $datetime = new \MongoDB\BSON\UTCDateTime($time * 1000);
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
}
