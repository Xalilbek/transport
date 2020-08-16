<?php

namespace Controllers;

use Custom\Models\Deliveries;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;
use const Multiple\ACTIVITY_SECTIONS;
use Models\Activities;
use Models\Calendar;
use Models\Deliveries;
use Models\DocumentFolders;
use Models\Parameters;
use Models\TempFiles;
use Models\Users;

class ListController extends \Phalcon\Mvc\Controller
{
    public function getUserLink($data)
    {
        return !$data ? '<span class="fw-600 text-pink">' . Lang::get("DeletedUser") . '</span>' : '<a class="fw-700 text-primary" target="_blank" href="/profile/' . $data->id . '">' . $data->fullname . '</a>';
    }

    public function getCalendarLink($data)
    {
        return !$data ? '<span class="fw-600 text-pink">' . Lang::get("DeletedCalendar") . '</span>' : '<a class="fw-700 text-primary" target="_blank" href="/calendar/edit/' . $data->_id . '">' . $data->title . '</a>';
    }

    public function getFileLink($data)
    {
        return !$data ? '<span class="fw-600 text-pink">' . Lang::get("DeletedFile") . '</span>' : '<a class="fw-700 text-primary" target="_blank" href="' . FILE_URL . '/upload/' . (string) $data->uuid . '/' . (string) $data->_id . '/' . $data->filename . '.' . $data->type . '?ref=' . microtime(true) . '">' . $data->filename . '.' . $data->type . '</a>';
    }

    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $id        = (string) trim($req['id']);
        $section   = (string) trim($req['section']);
        $operation = (string) trim($req['operation']);
        $user_id   = (int) trim($req['user_id']);

        if ($permissions['activities_view']['allow']) {

            $binds = [
                "is_deleted" => ['$ne' => 1],
            ];

            if (strlen($id) > 0) {
                $binds['values.id'] = Activities::objectId($id);
            }

            if ($section && in_array($section, ACTIVITY_SECTIONS)) {
                $binds['section'] = (string) $section;
            }

            if ($operation && Activities::getOperations($this->lang)[$operation]) {
                $binds['operation'] = (string) $operation;
            }

            if (in_array("self", $permissions['activities_view']['selected'])) {
                $binds['user_id'] = (string)Auth::getData()->_id;
            } else {
                if (is_numeric($user_id) && $user_id > 0) {
                    $binds['user_id'] = (int) $user_id;
                }
            }

            $conditions = [
                $binds,
            ];

            $sort_field = trim($req["sort"]);
            $sort_order = trim($req["sort_type"]);

            if (in_array($sort_field, ['user_id', 'section', 'operation', 'created_at'])) {
                $conditions["sort"][$sort_field] = $sort_order == 'desc' ? -1 : 1;
            }

            $skip  = (int) $req["skip"];
            $limit = (int) $req["limit"];

            if ($limit == 0) {
                $limit = 50;
            } else if ($limit > 200) {
                $limit = 200;
            }

            if (Req::get("limit") !== "-1") {
                $conditions = array_merge($conditions, [
                    "limit" => $limit,
                    "skip"  => $skip,
                ]);
            }

            $query = Activities::find($conditions);
            $count = Activities::count([
                $binds,
            ]);

            $data = [];
            if (count($query) > 0) {
                foreach ($query as $value) {
                    $who      = Users::getById($value->user_id);
                    $replaces = [
                        "{WHO}" => $this->getUserLink($who),
                    ];

                    if (in_array($value->section, ["calendar"])) {
                        $calendar = Calendar::findById(Calendar::objectId($value->values->calendar_id));
                        $replaces = array_merge($replaces, [
                            "{CALENDAR_TITLE}" => $this->getCalendarLink($calendar),
                        ]);
                        // calendar users activities
                        if (in_array($value->operation, ["calendar_users_add", "calendar_users_delete"])) {
                            $user     = Users::getById($value->values->user_id);
                            $replaces = array_merge($replaces, [
                                "{WHOM}" => $this->getUserLink($user),
                            ]);
                        }
                    } elseif (in_array($value->section, ["lessons", "permissions"])) {
                        $user     = Users::getById($value->values->id);
                        $replaces = array_merge($replaces, [
                            "{WHOM}"  => $this->getUserLink($user),
                            "{WHOSE}" => $this->getUserLink($user),
                        ]);
                    } elseif (in_array($value->section, ["parameters"])) {
                        $parameters = Parameters::findFirst([
                            [
                                "_id"  => $value->values->id,
                                "type" => (string) $value->values->type,
                            ],
                        ]);
                        $parametersType = Parameters::typeListByKey()[$value->values->type];
                        $replaces       = array_merge($replaces, [
                            "{CATEGORY}" => '<span class="fw-700">' . $parameters->titles->{Lang::getLang()} . '</span> (<span class="fw-600">' . $parametersType . '</span>)',
                        ]);
                    } elseif (in_array($value->section, ["documents"])) {
                        $documentFolder = DocumentFolders::findById($value->values->id);
                        $replaces       = array_merge($replaces, [
                            "{FOLDER_TITLE}" => '<span class="fw-700">' . $documentFolder->title . '</span>',
                        ]);
                        // file activities
                        if (in_array($value->operation, ["documents_file_upload", "documents_file_update", "documents_file_delete"])) {
                            $file     = TempFiles::findById($value->values->file_id);
                            $replaces = array_merge($replaces, [
                                "{FILE}" => $this->getFileLink($file),
                            ]);
                        }
                    } elseif (in_array($value->section, ["delivery"])) {
                        $delivery = Deliveries::findById($value->values->id);
                        $employee = Users::getById($delivery->employee_id);
                        $replaces = array_merge($replaces, [
                            "{SNIPPET}" => '<span class="fw-700">' . $employee->fullname . ', ' . $delivery->address . ', ' . $delivery->number . ', ' . $delivery->weight . '</span>',
                        ]);
                    }

                    $data[] = [
                        'id'         => (string) $value->_id,
                        'user_id'    => (int) $value->user_id,
                        'section'    => (string) $value->section,
                        'operation'  => (string) strtr(Activities::getOperations($this->lang)[$value->operation]["title"], $replaces),
                        'values'     => (array) $value->values,
                        'changes'    => (array) $value->changes,
                        'user'       => $who ? [
                            'id'       => $who->id,
                            'fullname' => $who->fullname,
                            'type'     => [
                                'text'  => Lang::get(ucfirst($who->type)),
                                'value' => $who->type,
                            ],
                        ] : false,
                        'created_at' => Activities::dateFormat($value->created_at, "Y-m-d H:i"),
                    ];
                }

                $response = array(
                    "status" => "success",
                    "data"   => $data,
                    "count"  => $count,
                    "skip"   => $skip,
                    "limit"  => $limit,
                );
            } else {
                $error = Lang::get("noInformation", "No information found");
            }
        } else {
            $error = Lang::get("PageNotAllowed");
        }

        if ($error) {
            $response = array(
                "status"      => "error",
                "error_code"  => 1023,
                "description" => $error,
            );
        }
        echo json_encode($response, true);
        exit();
    }
}
