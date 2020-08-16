<?php
namespace Lib;

class Permission
{
    public static $data;

    public static function init($crmType, $lang = false)
    {

    }

    public static function getPermissionSettings()
    {
        $data = [];

        $data["crm"] = [
            "title"       => Lang::get("CRM"),
            "permissions" => [
                "crm_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => true,
                ],
                "crm_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "crm_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "crm_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["businesses"] = [
            "title"       => Lang::get("Businesses"),
            "permissions" => [
                "businesses_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "businesses_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "businesses_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "businesses_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["calendar"] = [
            "title"       => Lang::get("Calendar"),
            "permissions" => [
                "calendar_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "all"           => Lang::get("All", "All"),
                        "self"          => Lang::get("OnlySelf", "Only self"),
                        "user"          => Lang::get("User"),
                        "employee"      => Lang::get("Employee"),
                        "moderator"     => Lang::get("Moderator"),
                        "partner"       => Lang::get("Partner"),
                        "his_users"     => Lang::get("HisUsers", "His/Her users"),
                        "his_employees" => Lang::get("HisEmployees", "His/Her employees"),
                    ],
                ],
                "calendar_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self"          => Lang::get("OnlySelf", "Only self"),
                        "user"          => Lang::get("User"),
                        "employee"      => Lang::get("Employee"),
                        "moderator"     => Lang::get("Moderator"),
                        "partner"       => Lang::get("Partner"),
                        "his_users"     => Lang::get("HisUsers", "His/Her users"),
                        "his_employees" => Lang::get("HisEmployees", "His/Her employees"),
                    ],
                ],
            ],
        ];

        $data["chat"] = [
            "title"       => Lang::get("Chat"),
            "permissions" => [
                "chat_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "chat_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self"          => Lang::get("OnlySelf", "Only self"),
                        "user"          => Lang::get("User"),
                        "employee"      => Lang::get("Employee"),
                        "moderator"     => Lang::get("Moderator"),
                        "partner"       => Lang::get("Partner"),
                        "his_users"     => Lang::get("HisUsers", "His/Her users"),
                        "his_employees" => Lang::get("HisEmployees", "His/Her employees"),
                    ],
                ],
            ],
        ];

        $data["parameters"] = [
            "title"       => Lang::get("Parameters"),
            "permissions" => [
                "parameters_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => true,
                    "selected" => [],
                    "default"  => [],
                    "values"   => [
                        "time_record_categories" => Lang::get("TimeRecordCategories"),
                        "price_list"             => Lang::get("PriceList"),
                        "lesson_categories"      => Lang::get("LessonCategories"),
                        "track_list"             => Lang::get("TrackList"),
                        "branchs"                => Lang::get("Branches"),
                        "cities"                 => Lang::get("Cities"),
                        "locations"              => Lang::get("Locations"),
                        "package_types"          => Lang::get("PackageTypes", "Package types"),
                        "timezones"              => Lang::get("TimeZones", "Timezones"),
                        "currencies"             => Lang::get("Currencies", "Currencies"),
                        "calendar_categories"    => Lang::get("CalendarCategories"),

                    ],
                ],
                "parameters_create" => [
                    "title"    => Lang::get("Add"),
                    "type"     => "checkbox",
                    "all"      => true,
                    "selected" => [],
                    "default"  => [],
                    "values"   => [
                        "time_record_categories" => Lang::get("TimeRecordCategories"),
                        "price_list"             => Lang::get("PriceList"),
                        "lesson_categories"      => Lang::get("LessonCategories"),
                        "track_list"             => Lang::get("TrackList"),
                        "branchs"                => Lang::get("Branches"),
                        "cities"                 => Lang::get("Cities"),
                        "locations"              => Lang::get("Locations"),
                        "package_types"          => Lang::get("PackageTypes", "Package types"),
                        "timezones"              => Lang::get("TimeZones", "Timezones"),
                        "currencies"             => Lang::get("Currencies", "Currencies"),
                        "calendar_categories"    => Lang::get("CalendarCategories"),

                    ],
                ],
                "parameters_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => true,
                    "selected" => [],
                    "default"  => [],
                    "values"   => [
                        "time_record_categories" => Lang::get("TimeRecordCategories"),
                        "price_list"             => Lang::get("PriceList"),
                        "lesson_categories"      => Lang::get("LessonCategories"),
                        "track_list"             => Lang::get("TrackList"),
                        "branchs"                => Lang::get("Branches"),
                        "cities"                 => Lang::get("Cities"),
                        "locations"              => Lang::get("Locations"),
                        "package_types"          => Lang::get("PackageTypes", "Package types"),
                        "timezones"              => Lang::get("TimeZones", "Timezones"),
                        "currencies"             => Lang::get("Currencies", "Currencies"),
                        "calendar_categories"    => Lang::get("CalendarCategories"),
                    ],
                ],
                "parameters_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => true,
                    "selected" => [],
                    "default"  => [],
                    "values"   => [
                        "time_record_categories" => Lang::get("TimeRecordCategories"),
                        "price_list"             => Lang::get("PriceList"),
                        "lesson_categories"      => Lang::get("LessonCategories"),
                        "track_list"             => Lang::get("TrackList"),
                        "branchs"                => Lang::get("Branches"),
                        "cities"                 => Lang::get("Cities"),
                        "locations"              => Lang::get("Locations"),
                        "package_types"          => Lang::get("PackageTypes", "Package types"),
                        "timezones"              => Lang::get("TimeZones", "Timezones"),
                        "currencies"             => Lang::get("Currencies", "Currencies"),
                        "calendar_categories"    => Lang::get("CalendarCategories"),

                    ],
                ],
            ],
        ];

        $data["cases"] = [
            "title"       => Lang::get("Cases"),
            "permissions" => [
                "cases_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "cases_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "cases_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "cases_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["contacts"] = [
            "title"       => Lang::get("Contacts"),
            "permissions" => [
                "contacts_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "contacts_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "contacts_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "contacts_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["notes"] = [
            "title"       => Lang::get("Notes"),
            "permissions" => [
                "notes_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "notes_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "notes_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "notes_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["documents"] = [
            "title"       => Lang::get("Documents"),
            "permissions" => [
                "documents_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "documents_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "documents_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "documents_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["activities"] = [
            "title"       => Lang::get("Activities"),
            "permissions" => [
                "activities_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "activities_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "activities_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "activities_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["transactions"] = [
            "title"       => Lang::get("Transactions"),
            "permissions" => [
                "transactions_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "transactions_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "transactions_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "transactions_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["invoices"] = [
            "title"       => Lang::get("Invoices"),
            "permissions" => [
                "invoices_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "invoices_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "invoices_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "invoices_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["translations"] = [
            "title"       => Lang::get("Translations"),
            "permissions" => [
                "translations_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "translations_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "translations_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "translations_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["users"] = [
            "title"       => Lang::get("Users"),
            "permissions" => [
                "users_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "users_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "users_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "users_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["employees"] = [
            "title"       => Lang::get("Employees"),
            "permissions" => [
                "employees_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "employees_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "employees_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "employees_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["moderators"] = [
            "title"       => Lang::get("Moderators"),
            "permissions" => [
                "moderators_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "moderators_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "moderators_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "moderators_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["partners"] = [
            "title"       => Lang::get("Partners"),
            "permissions" => [
                "partners_view"   => [
                    "title"    => Lang::get("View"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "partners_create" => [
                    "title"    => Lang::get("Create"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "partners_update" => [
                    "title"    => Lang::get("Update"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
                "partners_delete" => [
                    "title"    => Lang::get("Delete"),
                    "type"     => "checkbox",
                    "all"      => false,
                    "selected" => [],
                    "default"  => ["self"],
                    "values"   => [
                        "self" => Lang::get("OnlySelf", "Only self"),
                        "all"  => Lang::get("All"),
                    ],
                ],
            ],
        ];

        $data["allusers"] = [
            "title"       => Lang::get("AllUsers", "All users"),
            "permissions" => [
                "allusers_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "allusers_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "allusers_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "allusers_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["lessons"] = [
            "title"       => Lang::get("Lessons"),
            "permissions" => [
                "lessons_view"         => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "lessons_create"       => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "lessons_update"       => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "lessons_delete"       => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "lessons_users_add"    => [
                    "title"   => Lang::get("UserAdd", "User add"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "lessons_users_delete" => [
                    "title"   => Lang::get("UserDelete", "User Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["firstaid"] = [
            "title"       => Lang::get("FirstAid", "First Aid"),
            "permissions" => [
                "firstaid_view"         => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "firstaid_create"       => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "firstaid_update"       => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "firstaid_delete"       => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "firstaid_users_add"    => [
                    "title"   => Lang::get("UserAdd", "User add"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "firstaid_users_delete" => [
                    "title"   => Lang::get("UserDelete", "User Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["closetracks"] = [
            "title"       => Lang::get("CloseTracks", "Close tracks"),
            "permissions" => [
                "closetracks_view"         => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "closetracks_create"       => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "closetracks_update"       => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "closetracks_delete"       => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "closetracks_users_add"    => [
                    "title"   => Lang::get("UserAdd", "User add"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "closetracks_users_delete" => [
                    "title"   => Lang::get("UserDelete", "User Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["selfstudy"] = [
            "title"       => Lang::get("SelfStudy", "Self study"),
            "permissions" => [
                "selfstudy_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "selfstudy_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "selfstudy_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "selfstudy_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "selfstudy_users_delete" => [
                    "title"   => Lang::get("UserDelete", "User Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["slippery"] = [
            "title"       => Lang::get("SlipperyRoads", "Slippery roads"),
            "permissions" => [
                "slippery_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "slippery_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "slippery_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "slippery_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["businessprofile"] = [
            "title"       => Lang::get("BusinessProfile", "Business profile"),
            "permissions" => [
                "businessprofile_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "businessprofile_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "businessprofile_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "businessprofile_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["lessonsplan"] = [
            "title"       => Lang::get("LessonsPlan", "Lessons Plan"),
            "permissions" => [
                "lessonsplan_view" => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["deliveries"] = [
            "title"       => Lang::get("Deliveries"),
            "permissions" => [
                "deliveries_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => true,
                ],
                "deliveries_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "deliveries_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "deliveries_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["timerecords"] = [
            "title"       => Lang::get("TimeRecords"),
            "permissions" => [
                "timerecords_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => true,
                ],
                "timerecords_create" => [
                    "title"   => Lang::get("Create"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "timerecords_update" => [
                    "title"   => Lang::get("Update"),
                    "type"    => "boolean",
                    "default" => false,
                ],
                "timerecords_delete" => [
                    "title"   => Lang::get("Delete"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        $data["statistics"] = [
            "title"       => Lang::get("Statistics"),
            "permissions" => [
                "statistics_view"   => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => true,
                ],
            ],
        ];

        $data["accesslogs"] = [
            "title"       => Lang::get("AccessLogs", "Access logs"),
            "permissions" => [
                "accesslogs_view" => [
                    "title"   => Lang::get("View"),
                    "type"    => "boolean",
                    "default" => false,
                ],
            ],
        ];

        return $data;
    }

    public static function getPermissionsByCrm($userPermissions)
    {
        $data = [];

        foreach (Permission::getPermissionSettings() as $groupKey => $permissionGroup) {
            $permissions = [];
            foreach ($permissionGroup["permissions"] as $permissionKey => $permissionValue) {

                $permission        = $permissionValue;
                $permission["key"] = $permissionKey;
                //exit(json_encode($userPermissions));
                if ($userPermissions[$permissionKey]) {
                    foreach ($userPermissions[$permissionKey] as $conKey => $conValue) {
                        $permission[$conKey] = $conValue;
                    }

                } else {
                    $permission["allow"] = false;
                }

                $permissions[] = $permission;
            }

            if (count($permissions) > 0) {
                $data[] = [
                    "key"         => $groupKey,
                    "title"       => $permissionGroup["title"],
                    "permissions" => $permissions,
                ];
            }

        }

        return $data;
    }

    public static function getPermissionsByType($user, $permissionConstruct = [])
    {
        //exit(json_encode($permissonContruct));
        $type            = $user->type;
        $userPermissions = json_decode(json_encode($user->permissions), true);
        $data            = [];

        foreach (Permission::getPermissionSettings() as $groupKey => $permissionGroup) {
            $permissions = [];
            foreach ($permissionGroup["permissions"] as $permissionKey => $permissionValue) {
                if ($permissionConstruct[$permissionKey] && $permissionConstruct[$permissionKey]["allow"]) {
                    //$permission = $permissionValue;
                    $permission             = [];
                    $permission["key"]      = $permissionKey;
                    $permission["type"]     = $permissionValue["type"];
                    $permission["title"]    = $permissionValue["title"];
                    $permission["selected"] = $permissionValue["default"];
                    $values                 = [];
                    foreach ($permissionValue["values"] as $key => $value) {
                        if ($permissionConstruct[$permissionKey]["all"] === true || in_array($key, $permissionConstruct[$permissionKey]["selected"])) {
                            $values[$key] = $value;
                        }
                    }

                    //exit(json_encode($values));
                    if ($permissionValue["type"] == "checkbox") {
                        $permission["values"] = $values;
                    }

                    /**
                     * foreach ($permissonContruct[$permissionKey] as $conKey => $conValue)
                     * $permission[$conKey] = $conValue; */

                    //exit(json_encode($userPermissions));
                    if ($userPermissions[$permissionKey]) {
                        if ($userPermissions[$permissionKey]["allow"]) {
                            $permission["allow"] = true;
                            if (is_array($userPermissions[$permissionKey]["selected"])) {
                                $permission["selected"] = $userPermissions[$permissionKey]["selected"];
                            }

                            /**
                            if(!$permission["selected"] || count($permission["selected"]) < 1){
                            $permission["selected"] = $permissionConstruct[$permissionKey]["default"];
                            } */
                            $permission["all"] = !!$userPermissions[$permissionKey]["all"];
                        } else {
                            $permission["allow"] = false;
                        }

                    } else {
                        if ($permission["type"] == "boolean") {
                            $permission["allow"] = $permissionConstruct[$permissionKey]["default"] ? true : false;
                        } else {
                            $permission["allow"] = true;
                        }
                    }

                    $permissions[] = $permission;
                }
            }

            if (count($permissions) > 0) {
                $data[] = [
                    "key"         => $groupKey,
                    "title"       => $permissionGroup["title"],
                    "permissions" => $permissions,
                ];
            }

        }

        return $data;
    }

    public static function getPermissionsByUser($user, $permissionConstruct)
    {
        $type            = $user->type;
        $userPermissions = json_decode(json_encode($user->permissions), true);
        $data            = [];

        $permissions = [];
        foreach (Permission::getPermissionSettings() as $groupKey => $permissionGroup) {
            foreach ($permissionGroup["permissions"] as $permissionKey => $permissionValue) {
                if ($permissionConstruct[$permissionKey] && $permissionConstruct[$permissionKey]["allow"]) {
                    $permission = [];
                    if ($permissionConstruct[$permissionKey]["all"]) {
                        foreach ($permissionValue["values"] as $key => $value) {
                            $permissionConstruct[$permissionKey]["selected"][] = $key;
                        }
                    }

                    if ($userPermissions[$permissionKey]) {
                        if ($userPermissions[$permissionKey]["allow"]) {
                            $all                 = $userPermissions[$permissionKey]["all"];
                            $permission["allow"] = true;
                            if ($userPermissions[$permissionKey]["selected"]) {
                                $permission["selected"] = $userPermissions[$permissionKey]["selected"];
                            }

                            if ($all && $permissionConstruct[$permissionKey]["selected"]) {
                                $permission["selected"] = $permissionConstruct[$permissionKey]["selected"];
                            }

                        } else {
                            $permission = false;
                        }

                    } else {
                        if ($permissionValue["type"] == "boolean") {
                            $permission          = [];
                            $permission["allow"] = $permissionConstruct[$permissionKey]["default"] ? true : false;
                        } else {
                            if (count($permissionConstruct[$permissionKey]["default"]) > 0) {
                                $permission          = [];
                                $permission["allow"] = true;
                                if ($permissionConstruct[$permissionKey]["default"]) {
                                    $permission["selected"] = $permissionConstruct[$permissionKey]["default"];
                                }

                            } else {
                                $permission = false;
                            }
                        }
                    }

                    $permissions[$permissionKey] = $permission;
                }
            }
        }

        return $permissions;
    }

}
