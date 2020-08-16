<?php
namespace Controllers;

use Custom\Models\DocumentFolders;
use Custom\Models\Files;
use Custom\Models\TempFiles;
use Custom\Models\Users;
use Lib\Auth;
use Lib\Lang;
use Lib\Lib;
use Lib\Req;
use Models\DocumentFolders;

class UpdateController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $error = false;

        $field  = trim(Req::get("field"));
        $value  = trim(Req::get("value"));
        $action = trim(Req::get("action"));
        $phone  = trim(str_replace(["+", "-", " ", "_", ".", ","], "", Req::get("phone")));

        $update = [];
        if ($field == "firstname") {
            if ((strlen($value) < 1 || strlen($value) > 100)) {
                $error = Lang::get("FirstnameError", "Firstname is empty");
            } else {
                $update["firstname"] = $value;
                $update["fullname"] = $value." ".Auth::getData()->lastname;
            }
        } else if ($field == "lastname") {
            if ((strlen($value) < 1 || strlen($value) > 100)) {
                $error = Lang::get("LastnameError", "Lastname is empty");
            } else {
                $update["lastname"] = $value;
                $update["fullname"] = Auth::getData()->firstname." ".$value;
            }
        } else if ($field == "gender") {
            $gender           = trim(Req::get("value")) == "female" ? "female" : "male";
            $update["gender"] = $gender;
        } else if ($field == "phone") {
            if (!is_numeric($phone) || strlen($phone) < 10) {
                $error = Lang::get("PhoneError", "Phone is wrong. Only numbers required");
            } else {
                $update["phone"] = $phone;
            }
        } else if ($field == "pin") {
            $pin = trim($value);
            if (!is_numeric($pin) || strlen($pin) !== 4) {
                $error = Lang::get("PinError", "Pin code is wrong. Only numbers required");
            } else {
                $update["pin"] = $pin;
            }
        } else if ($field == "pin_require") {
            $pin_require           = (int) trim(Req::get("value")) == 1 ? 1 : 0;
            $update["pin_require"] = $pin_require;
        } else if ($field == "avatar") {
            $file_id  = trim(Req::get("value"));
            $tempfile = TempFiles::findById($file_id);

            if ($tempfile) {
                $file = Files::copyTempFile($tempfile, [
                    "parent_type" => "profile",
                    "parent_id"   => (string)Auth::getData()->_id,
                ]);

                $update["avatar"] = [
                    'id'      => $file->_id,
                    'avatars' => (array) $file->avatars,
                    'server'  => (string) $file->server,
                ];

            } else {
                $error = Lang::get("FileNotFound", "File not found");
            }

        } else if ($field == "email") {
            $email = trim(mb_strtolower($value));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = Lang::get("EmailError", "Email is wrong");
            } else {
                $update["email"]          = $email;
                $update["email_verified"] = 0;
            }
        } else if ($field == "username") {
            $username = trim(mb_strtolower($value));
            if (!Users::findFirst([
                [
                    "username"    => (string) $username,
                    "crm_type"    => ['$ne' => 0],
                    "business_id" => ['$ne' => 0],
                ],
            ])) {
                $error = Lang::get("UsernameExists", "Username is exists");
            } elseif (!Lib::checkUsername($username)) {
                $error = Lang::get("UsernameError", "Username is wrong");
            } else {
                $update["username"] = $username;
            }
        } else if ($field == "invoice_due_date") {
            $update["invoice_due_date"] = (int) $value;
        } else if ($field == "sign") {
            if ($value) {
                $tempfile = TempFiles::findById($value);

                if ($tempfile) {
                    $file = Files::copyTempFile($tempfile, [
                        "parent_type" => "lesson_sign",
                        "parent_id"   => (string)Auth::getData()->_id,
                    ]);

                    $update["sign_file_id"] = (string) $file->_id;
                }
            } else {
                $update["sign_file_id"] = null;
            }
        } else if ($field == "company_logo_id" && Auth::getData()->type == 'employee') {
            if ($value) {
                $tempfile = TempFiles::findById($value);

                if ($tempfile) {
                    $file = Files::copyTempFile($tempfile, [
                        "parent_type" => "company_logo",
                        "parent_id"   => (string)Auth::getData()->_id,
                    ]);

                    $update["company_logo_id"] = (string) $file->_id;
                }
            } else {
                $update["company_logo_id"] = null;
            }
        } else if ($field == "verified") {
            $file_ids = (array) Req::get("value");
            $error    = false;
            foreach ($file_ids as $i => $file_id) {
                $file = TempFiles::findById($file_id);
                if (!$file || $file && $file->for != "passport") {
                    $error = Lang::get("File" . ($i + 1) . "NotFound", "File " . ($i + 1) . " not found");
                }
            }
            if (!$error) {
                $folder_id = DocumentFolders::insert([
                    'parent_type' => "passport",
                    'parent_id'   => (string)Auth::getData()->_id,
                    'title'       => (string) Lang::get("Screenshots"),
                    'filecount'   => (int) count($file_ids),
                    'filesize'    => 0,
                    'is_deleted'  => 0,
                    'created_at'  => DocumentFolders::getDate(),
                ]);
                $filesize = 0;
                foreach ($file_ids as $file_id) {
                    if ($file_id) {
                        $tempfile = TempFiles::findById($file_id);
                        if ($tempfile) {
                            $file = Files::copyTempFile($tempfile, [
                                "parent_type" => "folder",
                                "parent_id"   => (string) $folder_id,
                            ]);

                            $filesize += $file->size;
                        }
                    }
                }
                if ($filesize > 0) {
                    DocumentFolders::update(["_id" => $folder_id], [
                        'filesize' => $filesize,
                    ]);
                }
                $update["verified"] = 2;
            }
        }

        if (!$error && count($update) > 0) {
            $update["updated_at"] = Users::getDate();

            Users::update(["_id" => Auth::getData()->_id], $update);

            $success = Lang::get("UpdatedSuccessfully");

            $response = [
                "status"      => "success",
                "description" => $success,
            ];
        } else {
            $error = Lang::get("AllFieldsEmpty", "All fields are empty");
        }

        if ($error) {
            $response = [
                "status"      => "error",
                "description" => $error,
                "error_code"  => 1021,
            ];
        }

        echo json_encode($response, true);
        exit();
    }
}
