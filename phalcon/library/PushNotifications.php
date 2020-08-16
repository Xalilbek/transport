<?php
namespace Lib;

use Lib\Lib;

class PushNotifications
{
    // (Android)API access key from Google API's Console.
    public static $API_ACCESS_KEY = 'AAAA6wzFTyk:APA91bEObxl1CwK981vVO1_A5j5X5_3To5ax0U9TloahXo8os25NprcBOWQQOY9wASM4UQspmjj70F4T_2bMtIi7QNqZWIvHEcW0YrOyhKGOFffTayym3b_1Te1wkKRzZLu6J2LtGkqo';
    // (iOS) Private key's passphrase.
    public static $passphrase = '123456789';
    // (Windows Phone 8) The name of our push channel.
    public static $channelName = "joashp";

    public static function android($data, $reg_id) {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        //$fcmUrl = 'https://android.googleapis.com/gcm/send';
        $token  = $reg_id;

        $notification = [
            'title' =>$data['mtitle'],
            'body' => $data['mdesc'],
            'icon' => "appicon"
            //'icon' =>'myIcon',
            //'sound' => 'mySound'
        ];
        $extraNotificationData = ["message" => $notification,"moredata" =>'dd'];

        $fcmNotification = [
            //'registration_ids' => $tokenList, //multple token array
           'to'            => $token, //single token
           // 'registration_ids' => [$reg_id],
            'notification'  => $notification,
            'data'          => $notification
            //'data'          => $extraNotificationData
        ];

        $headers = [
            'Authorization: key=' . self::$API_ACCESS_KEY,
            'Content-Type: application/json'
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        echo $result = curl_exec($ch);
        if ($result === false)
            die('Curl failedd: ' . curl_error($ch));
        curl_close($ch);


        return $result;


        $url = 'https://android.googleapis.com/gcm/send';
        $message = array(
            'title' => $data['mtitle'],
            'username' => $data['mtitle'],
            'foreground' => true,
            'userInteraction' => false,
            'message' => $data['mdesc'],
            'subtitle' => '',
            'tickerText' => '',
            'msgcnt' => 1,
            'vibrate' => 1,
            'data' => [],
        );

        $headers = array(
            'Authorization: key=' .self::$API_ACCESS_KEY,
            'Content-Type: application/json'
        );

        $fields = array(
            'registration_ids' => [$reg_id],
            'data' => $message,
            "android" => [ "priority" => "high" ],
        );
        return self::useCurl($url, $headers, json_encode($fields));
    }

    public static function iOS($data, $devicetoken, $userType=false)
    {
        echo $data['mtitle']."-".$data['mdesc']."<br/>";
        $deviceToken = $devicetoken;
        $ctx = stream_context_create();
        // ck.pem is your certificate file
        //stream_context_set_option($ctx, 'ssl', 'local_cert', '../certs/AppStore_Certificates.pem');
        $certFile = '../certs/pushcert.pem';
        stream_context_set_option($ctx, 'ssl', 'local_cert', $certFile);

        if(!file_exists($certFile)) echo($certFile.": no file found<br/>");

        stream_context_set_option($ctx, 'ssl', 'passphrase', self::$passphrase);
        // Open a connection to the APNS server
        $fp = stream_socket_client(
            'ssl://gateway.push.apple.com:2195', $err,
            //'ssl://gateway.sandbox.push.apple.com:2195', $err,
            $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp)
            return false;
        //exit("Failed to connect: $err $errstr" . PHP_EOL);
        // Create the payload body
        $body['aps'] = array(
            'alert' => array(
                'title' => $data['mtitle'],
                'body' => $data['mdesc'],
            ),
            'sound' => 'default',
            //'badge' => 1,
        );
        // Encode the payload as JSON
        $payload = json_encode($body);
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        fclose($fp);
        if (!$result)
            return 'Message not delivered' . PHP_EOL;
        else
            return 'Message successfully delivered' . PHP_EOL;
    }

    public function useCurl($url, $headers, $fields = null) {
        $ch = curl_init();
        if ($url) {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if ($fields)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            echo $result = curl_exec($ch);
            if ($result === false)
                die('Curl failed: ' . curl_error($ch));
            curl_close($ch);
            return $result;
        }
    }

}
