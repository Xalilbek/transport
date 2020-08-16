<?php
namespace Custom\Models;

use Lib\MainDB;

class TransactionItems extends MainDB
{
    public static function getSource(){
        return "transaction_items";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
    }
}