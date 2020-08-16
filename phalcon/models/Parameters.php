<?php
namespace Models;

use Lib\Lang;
use Lib\MainDB;

class Parameters extends MainDB
{
    public static function getSource()
    {
        return "parameters";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int) $id,
            ],
        ]);
    }

    public static function getByType($type)
    {
        return self::findFirst([
            [
                "type"       => (string) $type,
                "is_deleted" => 0,
            ],
        ]);
    }

    public static function getTitleByLang($data)
    {
        if($data->titles->{$lang}){
            return $data->titles->{$lang};
        } elseif($data->titles->{$data->default_lang}) {
            return $data->titles->{$data->default_lang};
        } else {
            foreach(Lang::getLangs() as $lang){
                if($data->titles->{$lang}){
                    return $data->titles->{$lang};
                }
            }
        }
    }

    public static function getNewId($type)
    {
        $last = self::findFirst([
            ["type" => $type],
            "sort" => ["id" => -1]
        ]);
        if ($last) {
            $id = $last->id + 1;
        } else {
            $id = 1;
        }
        return $id;
    }

    public static function typeList()
    {
        $list = [
            [
				'text' => Lang::get('CalendarCategories'),
				'value' => 'calendar_categories'
			],
			[
				'text' => Lang::get('Branchs'),
				'value' => 'branchs'
            ],
            [
				'text' => Lang::get('Cities'),
				'value' => 'cities'
            ],
            [
				'text' => Lang::get('Locations'),
				'value' => 'locations'
            ],
            [
				'text' => Lang::get('PackageTypes'),
				'value' => 'package_types'
            ],
            [
				'text' => Lang::get('Currencies'),
				'value' => 'currencies'
            ],
            [
				'text' => Lang::get('TimeZones'),
				'value' => 'timezones'
            ]
        ];
        
        if(CRM_TYPE == 2){
            $old_list = $list;
            $list = [
                [
                    'text' => Lang::get('LessonCategories'),
                    'value' => 'lesson_categories'
                ],
                [
                    'text' => Lang::get('TrackList'),
                    'value' => 'track_list'
                ],
            ];
            foreach($old_list as $value) {
                $list[] = $value;
            }
        } elseif(CRM_TYPE == 3){
            $old_list = $list;
            $list = [
                [
                    'text' => Lang::get('TimeRecordCategories'),
                    'value' => 'time_record_categories'
                ],
                [
                    'text' => Lang::get('PriceList'),
                    'value' => 'price_list'
                ],
            ];
            foreach($old_list as $value) {
                $list[] = $value;
            }
        } elseif(CRM_TYPE == 4){

        }

        return $list;
    }

    public static function typeListByKey()
    {
        $list = [];
        foreach(self::typeList() as $row){
            $list[$row['value']] = $row['text'];
        }
        return $list;
    }
}
