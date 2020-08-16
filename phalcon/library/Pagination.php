<?php
namespace Lib;

class Pagination
{
    static $page = 0;
    public static function offset( $count, $limit, $page )
    {
        self::$page = $page;
        if ( $page < 1 || $limit >= $count ) {
            $offset = 0;
        } else {
            $totalpages = ceil( $count / $limit );
            if ( $page > $totalpages ) {
                $offset = ($totalpages - 1) * $limit;
            } else {
                $offset = ($page - 1) * $limit;
            }
        }
        return $offset;
    }

    public static function pagenav( $url, $count, $limit )
    {
        $page = self::$page;

        $links = [];
        if ( $count > $limit ) {
            if ( $page < 1 ) {
                $page = 1;
            }
            $totalpages = ceil( $count / $limit );
            if ( $page >= $totalpages ) {
                $page = $totalpages;
            }

            if ( $page >= 1000 ) {
                $adjacents = 1;
            } elseif ( $page >= 100 ) {
                $adjacents = 2;
            } else {
                $adjacents = 2;
            }

            $links['total'] = $totalpages;
            $links['page']  = $page;

            if ( $page > 1 ) {
                $links['prev'] = $url . ($page - 1);
            }
            if ( $page < $totalpages ) {
                $links['next'] = $url . ($page + 1);
            }

            if ( $page > $adjacents ) {
                $start = $page - $adjacents;
                if ( $totalpages - $page < $adjacents ) {
                    if ( $page - $adjacents > 0 ) {
                        $start = $totalpages - $adjacents * 2;
                        if ( $start < 1 ) {
                            $start = 1;
                        }
                    }
                }
                $max = $page + $adjacents;
            } else {
                $start = 1;
                $max   = $adjacents * 2 + 1;
            }

            if ( $totalpages < $max ) {
                $max = $totalpages;
            }
            
            $i = $start;
            
            if($start > 0){
                $links['links'][1] = $url . 1;
                $links['links'][2] = 'dots';
            }

            while ( $i <= $max ) {
                $links['links'][$i] = $url . $i;
                $i++;
            }
            
            if($totalpages > $max){
                $links['links'][$i++] = 'dots';
                $links['links'][$totalpages] = $url . $totalpages;
            }
        }
        return $links;
    }
}