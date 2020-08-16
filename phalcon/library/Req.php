<?php

namespace Lib;


class Req
{
    public static $all;

    public static $get;

    public static $post;

    public static $server;

    public static function init()
    {
        $all        = [];
        $get        = [];
        $post       = [];
        $server     = [];

        $json       = file_get_contents('php://input');
        $input      = json_decode($json, true);

        foreach ($_GET as $key => $value){
            $get[$key] = $value;
            $all[$key] = $value;
        }

        foreach ($_POST as $key => $value){
            $post[$key] = $value;
            $all[$key] = $value;
        }

        foreach ($input as $key => $value){
            $post[$key] = $value;
            $all[$key] = $value;
        }

        foreach ($_REQUEST as $key => $value){
            $server[$key] = $value;
        }

        Req::$get      = $get;
        Req::$post     = $post;
        Req::$server   = $server;
        Req::$all      = $all;
        return true;
    }

    public static function get($key=false)
    {
        if($key && strlen($key) > 0){
            return Req::$all[$key];
        }else{
            return Req::$all;
        }
    }

    public static function getQuery($key=false)
    {
        if($key && strlen($key) > 0){
            return Req::$get[$key];
        }else{
            return Req::$get;
        }
    }

    public static function getPost($key=false)
    {
        if($key && strlen($key) > 0){
            return Req::$post[$key];
        }else{
            return Req::$post;
        }
    }

    public static function getServer($key=false)
    {
        if($key && strlen($key) > 0){
            return Req::$server[$key];
        }else{
            return Req::$server;
        }
    }
}