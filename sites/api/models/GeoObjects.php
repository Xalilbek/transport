<?php

namespace Custom\Models;

use Lib\MainDB;

class GeoObjects extends MainDB
{
    public static function getSource(){
        return "geo_objects";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int)$id
            ]
        ]);
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

    public static function getGeojson($type, $coordinates)
    {
//        var_dump($type);
//        var_dump(is_numeric($coordinates[0]));
//        var_dump($coordinates[0]);
//        var_dump(is_numeric($coordinates[1]));
//
//        var_dump($coordinates[1]);
//exit();
        $error = false;
        $geoJson = false;
        if($type == "marker" && is_numeric($coordinates['lat']) && is_numeric($coordinates['lng']))
        {
            $geoJson = [
                "type"	=> "Point",
                "coordinates"	=> [(float)$coordinates['lng'], (float)$coordinates['lat']]
            ];
        }
        elseif($type == "circle" && $coordinates['lat'] && $coordinates['lng'])
        {

            $geoJson = [
                "type"	=> "Point",
                "coordinates"	=> [(float)$coordinates['lng'], (float)$coordinates['lat']]
            ];
        }
        elseif($type == "polygon" && count($coordinates) > 2)
        {

            $fCoords = [];
            for ($i = 0 ; $i < count($coordinates)-1 ; $i+=2){


                if(is_numeric((float)$coordinates[$i]) && is_numeric((float)$coordinates[$i+1])){
                    $fCoords[] = [(float)$coordinates[$i][0], (float)$coordinates[$i+1][0]];
                }else{
                    $error = true;
                }

            }
            if(!$error)
            {
                if($fCoords[0][0] !== $fCoords[count($fCoords)-1][0] && $fCoords[0][1] !== $fCoords[count($fCoords)-1][1])
                    $fCoords[] = $fCoords[0];
                $geoJson = [
                    "type"			=> "Polygon",
                    "coordinates"	=> [$fCoords]
                ];
            }
        }

        return $geoJson;
    }


    public static function checkPointInGeozones($geozoneIds, $coords)
    {
        return self::findFirst([
            [
                "_id"        => ['$in' => $geozoneIds],
                "geometry"	=> [
                    '$geoIntersects'	=> [
                        '$geometry'	=> [
                            "type"          => "Point" ,
                            "coordinates"	=> $coords,
                        ]
                    ]
                ],
            ]
        ]);
    }



    public static function getPointsByIds($ids)
    {
        $points = GeoObjects::find([
            [
                "_id" => [
                    '$in' => $ids
                ]
            ]
        ]);

        return $points;
    }


}