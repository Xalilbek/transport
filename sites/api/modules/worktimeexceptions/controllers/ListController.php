<?php
namespace Controllers;

use Custom\Models\Parameters;
use Custom\Models\Users;
use Custom\Models\WorkTimeExceptions;
use Lib\Auth;
use Lib\Lang;
use Lib\Req;

class ListController extends \Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $permissions = Auth::getPermissions();

        $error = false;
        $req   = (array) Req::get();

        $user_id     = (string) trim($req['user_id']);
        $category_id = (string) trim($req['category_id']);

        if ($permissions['worktimeexceptions_view']['allow']) {
            $binds = [
                "is_deleted" => ['$ne' => 1],
            ];

            if ($user_id > 0) {
                $binds['user_id'] = (string) $user_id;
            }

            if (strlen($category_id) > 0) {
                $binds['category_id'] = (string) $category_id;
            }

            $sort_field = trim($req["sort"]);
            $sort_order = trim($req["sort_type"]);

            $sort = [];
            if (in_array($sort_field, ['user_id', 'start_date', 'end_date', 'created_at'])) {
                $sort[$sort_field] = $sort_order == 'desc' ? -1 : 1;
            }

            $skip  = (int) $req['skip'];
            $limit = (int) $req['limit'];

            if ($limit == 0) {
                $limit = 50;
            } else if ($limit > 200) {
                $limit = 200;
            }

            $query = WorkTimeExceptions::find([
                $binds,
                "skip"  => $skip,
                "limit" => $limit,
                "sort"  => $sort,
            ]);

            $count = WorkTimeExceptions::count([
                $binds,
            ]);

            $usersById = WorkTimeExceptions::listById($query, 'user_id', function ($ids) {
                return [
                    "col"  => "_id",
                    "rows" => Users::find([
                        [
                            "_id" => [
                                '$in' => array_map(function ($id) {
                                    return Users::objectId($id);
                                }, $ids),
                            ],
                        ],
                    ]),
                ];
            });


            $categoriesById = WorkTimeExceptions::listById($query, 'category_id', function ($ids) {
                return [
                    "col"  => "_id",
                    "rows" => Parameters::find([
                        [
                            "_id" => [
                                '$in' => array_map(function ($id) {
                                    return Parameters::objectId($id);
                                }, $ids),
                            ],
                        ],
                    ]),
                ];
            });

            $data = [];
            if (count($query) > 0) {
                foreach ($query as $value) {
                    $user     = $usersById[$value->user_id];
                    $category = $categoriesById[$value->category_id];

                    $data[] = [
                        'id'          => (string) $value->_id,
                        'user'        => $user ? [
                            "id"       => $user->id,
                            "fullname" => $user->fullname,
                        ] : [
                            "id"       => 0,
                            "fullname" => Lang::get("Deleted"),
                        ],
                        'description' => $value->description,
                        'category'    => [
                            "id" => (string)$category->_id,
                            "title" => (string)Parameters::getTitleByLang($category, Lang::getLang())
                        ],
                        'start_date'  => WorkTimeExceptions::dateFormat($value->start_date, "Y-m-d"),
                        'end_date'    => WorkTimeExceptions::dateFormat($value->end_date, "Y-m-d"),
                        'created_at'  => WorkTimeExceptions::dateFormat($value->created_at, "Y-m-d H:i"),
                    ];
                }

                $response = array(
                    $categoriesById,
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
            $error = Lang::get("PermissionsDenied");
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
