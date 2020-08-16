<?php
namespace Lib;

class Lib
{
    public static function arrayToObject($d) {
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return (object) array_map(__FUNCTION__, $d);
        }
        else {
            // Return object
            return $d;
        }
    }

    public static function objectToArray($d) {
        if (is_object($d)) {
            // Gets the properties of the given object
            // with get_object_vars function
            $d = get_object_vars($d);
        }
		
        if (is_array($d)) {
            /*
            * Return array converted to object
            * Using __FUNCTION__ (Magic constant)
            * for recursive call
            */
            return array_map(__FUNCTION__, $d);
        }
        else {
            // Return array
            return $d;
        }
    }

    public static function getAddress($lat, $lng) {
        return [
            "name" => "Copenhangen"
        ];
    }

    public static function nice_number_format( $str )
    {
        return preg_replace( '/(?!^)(?=(?>\d{3})+$)/', ' ', intval( $str ) );
    }

    public static function sec_to_time( $seconds )
    {
        $hour    = floor( $seconds / 3600 );
        $minute  = floor( $seconds % 3600 / 60 );
        $seconds = intval( $seconds % 60 );
        
        if ( $hour > 0 ) {
            return sprintf( "%d:%02d:%02d", $hour, $minute, $seconds );
        } else {
            return sprintf( "%d:%02d", $minute, $seconds );
        }
    }

    public static function isValidDate($date, $format= 'Y-m-d'){
        return $date == date($format, strtotime($date));
    }
    
    public static function fsize( $bytes )
	{
		if ( $bytes < 1024 ) {
			return $bytes . ' B';
		} elseif ( $bytes < 1048576 ) {
			return round( $bytes / 1024 , 2 ) . ' KB';
		} elseif ( $bytes < 1073741824 ) {
			return round( $bytes / 1048576 , 2 ) . ' MB';
		} elseif ( $bytes < 1099511627776 ) {
			return round( $bytes / 1073741824 , 2 ) . ' GB';
		} else {
			return round( $bytes / 1099511627776 , 2 ) . ' TB';
		}
    }
    
    public static function navigator($p_n, $sql_num, $limit, $url)
    {
        $nav="";
        if ($sql_num > $limit) {
            $pnum = $sql_num / $limit + 1;
            $pn = (int) $pnum;
            if ($pn == $pnum) {$pn=$pn-1;}
            $pn1 = $p_n/$limit;
            $pn1 = (int) $pn1;
            $pn0 = $pn1 - 2;
            if ($pn0 < 0) {$pn0=0;}
            $pn2 = $pn1 + 3;
            if ($pn2>$pn) {$pn2=$pn;}
            if ($pn1 !== 0) {
                $nav .= "<a class=\"btn-page btn-page-first\" href=\"" . $url . "".(($pn1-1)*$limit)."\">First</a>";
            }
            for ($i = $pn0; $i < $pn2; $i++) {
                if ($i == $pn1) {
                    $nav .= "<a class=\"btn-page btn-page-selected\" href=\"#\">" . ($i+1) . "</a>";
                } else {
                    $nav .= "<a class=\"btn-page\" href=\"" . $url . "" . ($i * $limit) . "\">" . ($i+1) . "</a>";
                }
            }
            if ($pn1 !== $pn - 1) {
                $nav .= "<a class=\"btn-page btn-page-last\" href=\"" . $url . "" . (($pn1+1)*$limit) . "\">Last</a>";
            }
        }
        return $nav;
    }

    // ################################ FILTER SYMBOLS ################################
    public static function filterSymbols($text)
    {
        $arr1=array('"', "'", '>', '<', '\\');
        $arr2=array("&#34;", "&#39;", '&#62;', '&#60;', '&#92;');
        $text = str_replace($arr1, $arr2, $text);
        return $text;
    }

    public static function timeToSeperate($real_time)
    {
        $time = time() - $real_time;
        $time_counted=0;
        $echo = "";
        if ($real_time < strtotime(date("Y-m-d 00:00:00"))){
            $echo = date("Y-m-d H:i", $real_time);
        }else{
            $echo = date("H:i", $real_time);
        }
        /*
        $days = (int) ($time / (24*3600));
            $time_counted+=$days*24*3600;
        $hours = (int) (($time-$time_counted)/3600);
            $time_counted+=$hours*3600;
        $minutes = (int) (($time-$time_counted)/60);
            $time_counted+=$minutes*60;
        $seconds = $time-$time_counted;
        if ($days>0){
            $rT .= $days.' gun ';
        }else if ($hours>0){
            $rT .= $hours.' saat ';
        }else if ($minutes>=0){
            $rT .= $minutes.' deq ';
        }else{
            if ($seconds>0)$rT .= $seconds.' san ';
        }
        $rT .= " evvel";
         *
         */
        return $echo;
    }

    public static function checkJavascript()
    {
        $keys = ['android', 'ios', 'blackberry', 'chrome', 'mozilla', 'linux', 'windows'];
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $trimmed = str_replace($keys, "", $agent);
        return (strlen($agent) > strlen($trimmed)) ? true: false;
    }

    public static function initCurl($url, $vars_array, $method)
    {
        $method = strtoupper($method);
        $ch = \curl_init();
        $var_fields = "";
        FOREACH ($vars_array AS $key => $value){
            $var_fields .= $key.'='.urlencode($value).'&';
        }
        IF ($method == "POST"){
            $post_vars = $var_fields;
        }ELSE{
            $get_vars = (strlen($var_fields) > 0) ? "?".$var_fields: "";
            $url .= $get_vars;
        }
        curl_setopt($ch, CURLOPT_URL,$url);
        IF ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vars);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT , 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if(substr($url,0,5) == "https"){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
        }
        $response = curl_exec ($ch);
        if (curl_errno($ch) > 0){
            //exit(curl_error($ch));
        }
        curl_close ($ch);
        return $response;
    }

    public static function checkUsername($login)
    {
        if(!preg_match('/^[a-zA-Z0-9]{4,30}+$/i', $login)){
            return false;
        }else if(is_numeric(substr($login,0,1))){
            return false;
        }else{
            return true;
        }
    }

    public static function checkDate($d, $m, $y)
    {
        if (isset($d) && isset($m) && isset($y)) {
            if (substr($d,0,1)=='0'){
                $d=substr($d,1);
            }
            if (substr($m,0,1)=='0'){
                $m=substr($m,1);
            }
            if ($d > 0 && $d < 10 ) {
                $day = "0" . $d;
            } else if ($d > 0 && $d < 32) {
                $day = $d;
            } else {
                return false;
            }
            if ($m > 0 && $m < 10) {
                $month = "0" . $m;
            } else if ($m > 0 && $m < 13) {
                $month = $m;
            } else {
                return false;
            }
            if ($y > 1900 && $y < 2013) {
                $year = $y;
            } else {
                return false;
            }
            if (($m == "04" || $m == "06" || $m == "09" || $m == "11") && $d == 31) {
                return false;
            } else if ($m == "02" && $d > 29) {
                return false;
            } else {
                if ($day && $month && $year) {
                    $bd = $year.'-'.$month.'-'.$day;
                    return $bd;
                }
            }
        } else {
            return false;
        }
    }

    public static function getAge($date)
    {
        $currentTime = time();
        $dateTime = strtotime($date);
        $diff = $currentTime - $dateTime;
        $scale = (int)($diff/365/24/3600);
        return $scale;
    }

    public static function showDate($date, $type=0)
    {
        $time = strtotime($date);
        $out = "";
        if (date("Y-m-d") == date("Y-m-d", $time)){
            if ($type == 1) $out .= "bugun ";
            $out .= date("H:i", $time);
        }else if (date("Y-m-d", time() - 24*3600) == date("Y-m-d", $time)){
            $out = "dunen ".date("H:i", $time);
        }else{
            $out = $date;
        }
        return $out;
    }

    public static function generatePassword($password)
    {
        return md5("@#$%".sha1($password));
    }


    public static function sendSMSua($number, $text)
    {
        /*
         * 787 - Bekar.az
         * 851 - Azklub.az
         * 712 azcard
         */
        $url = "https://api.mobizon.com/service/message/sendsmsmessage?apiKey=3380004fffef993d33aca9318319810b208c7571&recipient=".$number."&text=".urlencode($text);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($ch);
        curl_close($ch);

        $is_sent = (substr(trim(strip_tags($output)),0,2) == 'Ok') ? 1: 0;

        $S                  = new MongoSmslog();
        $S->id              = (float)MongoSmslog::getNewId();
        $S->url             = (string)$url;
        $S->destination     = (string)$number;
        $S->response        = trim($output);
        $S->description     = $text;
        $S->is_sent         = (int)$is_sent;
        $S->attempt_count   = 1;
        $S->operator        = 2;
        $S->created_at      = new \MongoDate(time());
        $S->save();

        if($is_sent){
            return true;
        }else{
            return false;
        }
    }


    public static function secToStr($inputSeconds)
    {
        $elapse     = time() - $inputSeconds;

        $months    = (int)($elapse/(30*24*3600));
        $days       = (int)($elapse/(24*3600));
        $hours      = (int)($elapse/3600);
        $minutes    = (int)($elapse/60);
        $date_text  = "";
        if($months > 0){
            $date_text .= $months." month(s) ";
        }else if($days > 0){
            $date_text .= $days." day(s) ";
        }else if($hours > 0){
            $date_text .= $hours." hour(s) ";
        }else if($minutes > 0){
            $date_text .= $minutes." minute(s) ";
        }else{
            $date_text .= $elapse." second(s) ";
        }
        $date_text .= "ago";
        return trim($date_text);
    }

    public static function durationToStr($elapse)
    {
        $months    = (int)($elapse/(30*24*3600));
        $days       = (int)($elapse/(24*3600));
        $hours      = (int)($elapse/3600);
        $minutes    = (int)($elapse/60);
        $date_text  = "";
        if($months > 0){
            $date_text .= $months." month(s) ";
        }else if($days > 0){
            $date_text .= $days." day(s) ";
        }else if($hours > 0){
            $date_text .= $hours." hour(s) ";
        }else if($minutes > 0){
            $date_text .= $minutes." minute(s) ";
        }else{
            $date_text .= $elapse." second(s) ";
        }
        $date_text .= "";
        return trim($date_text);
    }


    public static function hmac($key, $data)
    {
        $b = 64;
        if (strlen($key) > $b){
            $key = pack("H*",md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;
        return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }

    public static function findAgeByDate($birthDate)
    {
        $from = new \DateTime($birthDate);
        $to   = new \DateTime('today');
        return $from->diff($to)->y;
    }

    public static function chatTime( $inputSeconds) {
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
            $date_text = date("H:i", $inputSeconds);
        }elseif($inputSeconds > time() - 90*86400){
            $date_text = date("d M H:i", $inputSeconds);
        }else{
            $date_text = date("Y-m-d H:i:s", $inputSeconds);
        }
        return trim($date_text);
    }

    public static function floatToDanish($amount, $round=-1)
    {
        if($round >= 0){
            $amount = (string)round($amount, $round);
        }else{
            $amount = (string)$amount;
        }
        $amount = str_replace(".", ",", $amount);
        return $amount;
    }

    public static function danishToFloat($amount)
    {
        $amount = (float)str_replace(",", ".", $amount);
        return $amount;
    }

    public static function getFilteredDate($date, $timeformat){
        if($timeformat == "da" && strlen($date) > 2){
            $date = substr($date, 6, 4)."-".substr($date, 3, 2)."-".substr($date, 0, 2);
        }
        return $date;
    }

    public static function getIp()
    {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
            $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    public static function xss_clean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do
        {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }
        while ($old_data !== $data);

        // we are done...
        return $data;
    }


    public static function jsonBeautify($json){
            $result      = '';
            $pos         = 0;
            $strLen      = strlen($json);
            $indentStr   = '  ';
            $newLine     = "\n";
            $prevChar    = '';
            $outOfQuotes = true;

            for ($i=0; $i<=$strLen; $i++) {

                // Grab the next character in the string.
                $char = substr($json, $i, 1);

                // Are we inside a quoted string?
                if ($char == '"' && $prevChar != '\\') {
                    $outOfQuotes = !$outOfQuotes;

                    // If this character is the end of an element,
                    // output a new line and indent the next line.
                } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                    $result .= $newLine;
                    $pos --;
                    for ($j=0; $j<$pos; $j++) {
                        $result .= $indentStr;
                    }
                }

                // Add the character to the result string.
                $result .= $char;

                // If the last character was the beginning of an element,
                // output a new line and indent the next line.
                if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                    $result .= $newLine;
                    if ($char == '{' || $char == '[') {
                        $pos ++;
                    }

                    for ($j = 0; $j < $pos; $j++) {
                        $result .= $indentStr;
                    }
                }

                $prevChar = $char;
            }

            return $result;
    }
}