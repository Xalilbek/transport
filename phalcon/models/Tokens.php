<?php
namespace Models;

use Lib\ApiDB;

class Tokens extends ApiDB
{
    public static function getSource(){
        return "user_tokens";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }

    public static function getDataByToken($token) 
    {
        $query = [
            "token" => $token,
            "server_token" => SERVER_TOKEN,
            "_env" => _ENV
        ];

        $ch = curl_init(CRM_URL . "/auth/getdata?" . http_build_query($query));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);

        if ($result) {
            return json_decode($result);
        }
    }
}