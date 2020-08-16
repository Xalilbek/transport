<?php
namespace Models;

use Lib\ApiDB;

class Blocks extends ApiDB
{
    public static function getSource(){
        return "blocks";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }

    public static function checkBlock($myId, $targetId, $cache=true)
    {
        $block  = false;
        $ckey   = md5("block_".$myId."_".$targetId);
        if($cache)
            $block = Cache::get($ckey);
        if(!$block)
        {
            $block = Blocks::findFirst([
                [
                    '$or' => [
                        [
                            "user_id"       => (int)$myId,
                            "target_id"     => (int)$targetId,
                            "is_deleted"    => ['$ne' => 1]
                        ],
                        [
                            "user_id"       => (int)$targetId,
                            "target_id"     => (int)$myId,
                            "is_deleted"    => ['$ne' => 1]
                        ]
                    ]
                ]
            ]);

            Cache::set($ckey, $block, time()+3600);
        }

        return $block;
    }



    public static function addBlock($myId, $targetId)
    {
        $block           = Blocks::findFirst([
            [
                "user_id"       => (int)$myId,
                "target_id"     => (int)$targetId,
                "is_deleted"    => ['$ne' => 1]
            ]
        ]);

        if(!$block)
        {
            $Insert = [
                "user_id"						=> (int)$myId,
                "target_id"					    => $targetId,
                "created_at"					=> self::getDate()
            ];

            Blocks::insert($Insert);
        }
        return true;
    }


    public static function removeBlock($myId, $targetId)
    {
        Blocks::deleteRaw(
            [
                "user_id"       => (int)$myId,
                "target_id"     => (int)$targetId,
            ]);
        $ckey   = md5("block_".$myId."_".$targetId);
        Cache::set($ckey, false, 0);
        return true;
    }

    public static function getNewId()
    {
        $last = self::findFirst(["sort" => ["id" => -1]]);
        if ($last) {
            $id = $last->id + 1;
        } else {
            $id = 1;
        }
        return $id;
    }
}