<?php
namespace Controllers;

use Custom\Models\Cache;
use Custom\Models\ForgetLogs;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;
use Models\ForgetLogs;

class Emailverifystep1Controller extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $error = false;

        $email = trim(strtolower(Req::get("email")));

        if (Cache::is_brute_force("forgot-" . $email, ["minute" => 10, "hour" => 20, "day" => 40])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } elseif (Cache::is_brute_force("authIn-" . Req::getServer("REMOTE_ADDR"), ["minute" => 30, "hour" => 250, "day" => 500])) {
            $error = Lang::get("AttemptReached", "You attempted many times. Please wait a while and try again");
        } else {
            $data = Auth::getData();

            if (!$data) {
                $error = Lang::get("EmailNotFound", "Email doesnt exists");
            } else {
                $data->email          = $email;
                $data->email_verified = 0;
                $data->save();

                $L = ForgetLogs::findFirst([
                    [
                        "email"      => $email,
                        //"msisdn"    => $msisdn,
                        "created_at" => [
                            '$gt' => time() - 24 * 3600,
                        ],
                    ],
                    "sort" => [
                        "created_at" => -1,
                    ],
                ]);

                if ($L && Users::toSeconds(@$L->created_at) < time() - 24 * 3600) {
                    $L->delete();
                    $L = false;
                }

                if (!$L) {
                    $code = rand(111111, 999999);
                    //$code = 123456;
                    $hash = md5($email . "-" . microtime(true));

                    $L = new ForgetLogs();
                    //$L->id             = ForgetLogs::getNewId();
                    $L->code        = (string) $code;
                    $L->hash        = $hash;
                    $L->status      = 1;
                    $L->check_limit = 0;
                } else {
                    $L->check_limit = 0;
                }

                if (@$L->sms_count < 3) {
                    @$L->sms_count += 1;

                    $layout  = Lang::get("VerificationCode", "Verification Code") . ': ' . $L->code;
                    $mailUrl = EMAIL_DOMAIN;
                    $vars    = [
                        "key"     => "q1w2e3r4t5aqswdefrgt",
                        "from"    => DEFAULT_EMAIL,
                        "to"      => $email,
                        "subject" => Lang::get("VerificationCode", "Verification Code"),
                        "content" => $layout,
                    ];

                    $response = Lib::initCurl($mailUrl, $vars, "post");

                    $L->response = mb_substr($response, 0, 2000);
                }

                $L->email      = $email;
                $L->created_at = Users::getDate();
                $L->save();

                $text     = Lang::get("VerificationCodeSend", "Verification code was sent to your email");
                $response = [
                    "status"      => "success",
                    //"verify_hash" => (string)$hash,
                    "description" => (string) $text,
                ];
            }
        }

        if ($error) {
            $response = [
                "status"      => "error",
                "error_code"  => 1017,
                "description" => $error,
            ];
        }
        echo json_encode((object) $response);
        exit;
    }
}
