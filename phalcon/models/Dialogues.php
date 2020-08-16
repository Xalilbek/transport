<?php
namespace Models;

use Lib\ApiDB;
use Lib\Lang;

class Dialogues extends ApiDB
{
    public static function getSource(){
        return "dialogues";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }
    public static function getDialogue($users, $status=0, $message=false)
    {
        $userIds = [];
        foreach($users as $userId)
            $userIds[] = (int)$userId;

        $dialogue = self::checkDialogueExist($userIds);
        if($dialogue)
        {
            return $dialogue;
        }
        else
        {
            $dialogueId = self::createDialogue($userIds, $status, $message);
            return self::findById($dialogueId);
        }
    }

    public static function checkDialogueExist($userIds, $cache=false)
    {
        $dialogue = false;
        $ckey = md5("dia_".json_encode($userIds));
        if($cache)
            $dialogue = Cache::get($ckey);
        if(!$dialogue && count($userIds) == 2)
        {
            $dialogue = self::findFirst([
                [
                    "users" => [
                        '$all'   => $userIds
                    ],
                    "type"  => "dialogue"
                ],
                "object"	=> true,
            ]);
            Cache::set($ckey, $dialogue, time()+3600);
        }
        return $dialogue;
    }

    public static function createDialogue($users, $status=0, $message="", $title=false)
    {
        $userIds = [];
        foreach($users as $userId)
            $userIds[] = (int)$userId;
        $insert = [
            "creator_id"    => $userIds[count($userIds)-1],
            "users"         => $userIds,
            "title"         => ($title) ? $title: null,
            "type"          => count($userIds) == 2 ? "dialogue": "group",
            //"updated_at"    => self::getDate(),
            "message"		=> [
                "id"	=> null,
                "body"	=> mb_strlen($message) > 50 ? mb_substr($message, 0, 50)."...": $message,
                "type"	=> "text",
            ],
            "created_at"    => self::getDate(),
        ];
        $dialogueId = self::insert($insert);

        foreach($userIds as $userId)
            DialogueUsers::createUserForDialogue($dialogueId, $userId, $status);

        return $dialogueId;
    }






    public static function chatTime( $inputSeconds) {
        $inputSeconds += TIME_DIFF;
        if(date("Y-m-d", $inputSeconds) == date("Y-m-d")){
            $date_text = date("H:i", $inputSeconds);
        }elseif(date("Y-m-d", $inputSeconds) == date("Y-m-d", time() - 86400)){
            $date_text = Lang::get("Yesterday");
        }else{
            $date_text = date("d/m/y", $inputSeconds);
        }
        return trim($date_text);
    }

    public static function messageTime( $inputSeconds) {
        if(date("Y-m-d", $inputSeconds) == date("Y-m-d")){
            $date_text = Lang::get("Today");
        }elseif(date("Y-m-d", $inputSeconds) == date("Y-m-d", time() - 24*3600)){
            $date_text = Lang::get("Yesterday");
        }else{
            $date_text = date("d M", $inputSeconds);
        }
        return trim($date_text);
    }



    public static function sendRegMessages( $userId, $type="user")
    {
        $message = Lang::get("RegistrationWelcomeMessage", "Hi, Congratulations! You registered successfully");
        $dialogue = self::getDialogue([1, $userId], 4, $message);

        if($dialogue)
        {
            if($type == "driver")
            {
                // ##################### DRIVER COMMANDS ###################
                $msgIns = [
                    "dialogue"		=> (string)$dialogue->_id,
                    "user"			=> 1,
                    "body"			=> "",
                    "ref"			=> null,
                    "type"			=> "command",
                    "command"		=> "driver_photos",
                    "is_deleted"	=> 0,
                    "created_at"	=> self::getDate()
                ];
                DialogueMessages::insert($msgIns);

                $msgIns = [
                    "dialogue"		=> (string)$dialogue->_id,
                    "user"			=> 1,
                    "body"			=> "",
                    "ref"			=> null,
                    "type"			=> "command",
                    "command"		=> "driver_info",
                    "is_deleted"	=> 0,
                    "created_at"	=> self::getDate()
                ];
                DialogueMessages::insert($msgIns);



                // ##################### CAR COMMANDS ###################
                $msgIns = [
                    "dialogue"		=> (string)$dialogue->_id,
                    "user"			=> 1,
                    "body"			=> "",
                    "ref"			=> null,
                    "type"			=> "command",
                    "command"		=> "car_photos",
                    "is_deleted"	=> 0,
                    "created_at"	=> self::getDate()
                ];
                DialogueMessages::insert($msgIns);

                $msgIns = [
                    "dialogue"		=> (string)$dialogue->_id,
                    "user"			=> 1,
                    "body"			=> "",
                    "ref"			=> null,
                    "type"			=> "command",
                    "command"		=> "car_info",
                    "is_deleted"	=> 0,
                    "created_at"	=> self::getDate()
                ];
                DialogueMessages::insert($msgIns);
            }


            $msgIns = [
                "dialogue"		=> (string)$dialogue->_id,
                "user"			=> 1,
                "ref"			=> null,
                "body"			=> $message,
                "type"			=> "text",
                "command"		=> false,
                "is_deleted"	=> 0,
                "created_at"	=> self::getDate()
            ];
            $msgId = DialogueMessages::insert($msgIns);


            /**
            $dialogueUpdate = [
                "updated_at"	=> self::getDate(),
                "message"		=> [
                    "id"	=> (string)$msgId,
                    "body"	=> strlen($message) > 50 ? substr($message, 0, 50)."...": $message,
                    "type"	=> "text",
                ],
            ];

            Dialogues::update(
                [
                    "_id"	=> $dialogue->_id,
                ],
                $dialogueUpdate
            ); */

            DialogueUsers::update(
                 [
                      "dialogue"        => (string)$dialogue->_id,
                      "user_id"			=> 1,
                 ],
                 [
                    "status"    => 0
                 ]
            );
        }
    }

    public static function getPermissions($dialogue, $user, $user_id)
    {
        $permissions = [];
        $construct = self::permissionList();

        foreach ($construct as $key => $value)
        {
            $isPermitted = $value["default"];
            if($user || $dialogue)
            {
                if((int)$dialogue->creator_id == (int)$user_id)
                {
                    $isPermitted = true;
                }
                else
                {
                    if(@$user && @$user->permissions && @$user->permissions->{$key} !== null){
                        $isPermitted = @$user->permissions->{$key};
                    }elseif(@$dialogue && @$dialogue->permissions && @$dialogue->permissions->{$key} !== null)
                        $isPermitted = @$dialogue->permissions->{$key};
                }
            }

            $permissions[$key] = $isPermitted;
        }
        return $permissions;
    }

    public static function permissionList($lang = false)
    {
        return [
            "send_text" => [
                "value"     => "send_text",
                "title"     => $lang ? Lang::get("CanSendText", "Can send text") : "",
                "default"   => true,
            ],
            "send_media" => [
                "value"     => "send_media",
                "title"     => $lang ? Lang::get("CanSendMedia", "Can send media") : "",
                "default"   => true,
            ],
            "add_user" => [
                "value"     => "add_user",
                "title"     => $lang ? Lang::get("CanAddUser", "Can add user") : "",
                "default"   => true,
            ],
            "remove_user" => [
                "value"     => "remove_user",
                "title"     => $lang ? Lang::get("CanRemoveUser", "Can remove user") : "",
                "default"   => false,
            ],
            "set_permission" => [
                "value"     => "set_permission",
                "title"     => $lang ? Lang::get("CanSetPermission", "Can set permission") : "",
                "default"   => false,
            ],
            "edit" => [
                "value"     => "edit",
                "title"     => $lang ? Lang::get("CanEdit", "Can edit") : "",
                "default"   => false,
            ],
        ];
    }
}