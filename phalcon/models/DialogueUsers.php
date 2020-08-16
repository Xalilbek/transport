<?php
namespace Models;

use Lib\ApiDB;
use Models\Dialogues;

class DialogueUsers extends ApiDB
{
    /**
     * status
     * -1 => left group,
     * 0 => deleted,
     * 1 => muted
     * 2 => active
     * 3 => has unread message
     * 4 => has unnotified message
     */
    public static function getSource(){
        return "dialogue_users";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }

    public static function getDialogue($dialogueId, $userId)
    {
        // new_messages_count
        return self::findFirst([
            [
                "dialogue"          => (string)$dialogueId,
                "user_id"           => (int)$userId,
            ]
        ]);
    }

    public static function createUserForDialogue($dialogueId, $userId, $status=3, $level=0, $creatorId=0)
    {

        $insert = [
            "dialogue"          => (string)$dialogueId,
            "user_id"           => (int)$userId,
            "status"            => (int)$status,
            "new_messages_count"=> (int)0,
            "level"             => (int)$level,
            "read_at"           => self::getDate(1),
            "deleted_at"        => self::getDate(1),
            "notified_at"       => self::getDate(1),
            "updated_at"        => self::getDate(),
            "joint_at"          => self::getDate(),
            "created_at"        => self::getDate(),
            "creator_id"        => (int)$creatorId,
        ];

        return self::insert($insert);
    }
}