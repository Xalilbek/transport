<?php
namespace Models;

use Lib\Auth;
use Lib\Lang;
use Lib\MainDB;

class Activities extends MainDB
{
    public static function getOperations()
    {
        return [
            // authentication
            "auth_sign_in"          => ["title" => Lang::get("WhoSignedIn", "{WHO} signed in")],
            "auth_sign_up"          => ["title" => Lang::get("WhoSignedUp", "{WHO} was registered")],
            "auth_sign_out"         => ["title" => Lang::get("WhoSignedOut", "{WHO} signed out")],
            "auth_recover_password" => ["title" => Lang::get("WhoRecoveredPassword", "{WHO} recovered password")],

            // calendar
            "calendar_create"       => ["title" => Lang::get("WhoCreatedCalendar", "{WHO} created calendar {CALENDAR_TITLE}")],
            "calendar_update"       => ["title" => Lang::get("WhoUpdatedCalendar", "{WHO} updated calendar {CALENDAR_TITLE}")],
            "calendar_delete"       => ["title" => Lang::get("WhoDeletedCalendar", "{WHO} deleted calendar {CALENDAR_TITLE}")],
            "calendar_users_add"    => ["title" => Lang::get("WhoAddedWhomToCalendar", "{WHO} added {WHOM} to calendar {CALENDAR_TITLE}")],
            "calendar_users_delete" => ["title" => Lang::get("WhoDeletedWhomFromCalendar", "{WHO} deleted {WHOM} from calendar {CALENDAR_TITLE}")],

            // parameters
            "parameters_create"     => ["title" => Lang::get("WhoCreatedNewParameters", "{WHO} created new {CATEGORY}")],
            "parameters_deleted"    => ["title" => Lang::get("WhoDeletedParameters", "{WHO} deleted {CATEGORY}")],
            "parameters_update"     => ["title" => Lang::get("WhoUpdatedParameters", "{WHO} updated {CATEGORY}")],

            // lessons
            "lessons_create"        => ["title" => Lang::get("WhoCreatedLesson", "{WHO} created {SNIPPET}")],
            "lessons_update"        => ["title" => Lang::get("WhoUpdatedLesson", "{WHO} update {SNIPPET}")],
            "lessons_delete"        => ["title" => Lang::get("WhoDeletedLesson", "{WHO} deleted {SNIPPET}")],
            "lessons_users_add"     => ["title" => Lang::get("WhoAddedWhomToLesson", "{WHO} added {WHOM} to lesson {SNIPPET}")],
            "lessons_users_delete"  => ["title" => Lang::get("WhoDeletedWhomFromLesson", "{WHO} deleted {WHOM} from lesson {SNIPPET}")],

            // users
            "users_create"          => ["title" => Lang::get("WhoCreatedUser", "{WHO} created new user {SNIPPET}")],
            "users_update"          => ["title" => Lang::get("WhoUpdatedUser", "{WHO} updated user {SNIPPET}")],
            "users_delete"          => ["title" => Lang::get("WhoDeletedUser", "{WHO} deleted user {SNIPPET}")],

            // cases
            "cases_create"          => ["title" => Lang::get("WhoCreatedCase", "{WHO} created new case {SNIPPET}")],
            "cases_update"          => ["title" => Lang::get("WhoUpdatedCase", "{WHO} updated case {SNIPPET}")],
            "cases_delete"          => ["title" => Lang::get("WhoDeletedCase", "{WHO} deleted case {SNIPPET}")],

            // partners
            "partners_create"       => ["title" => Lang::get("WhoCreatedPartner", "{WHO} created new partner {SNIPPET}")],
            "partners_update"       => ["title" => Lang::get("WhoUpdatedPartner", "{WHO} updated partner {SNIPPET}")],
            "partners_delete"       => ["title" => Lang::get("WhoDeletedPartner", "{WHO} deleted partner {SNIPPET}")],

            // notefolders
            "notefolders_create"    => ["title" => Lang::get("WhoCreatedNoteFolder", "{WHO} created new note folder {SNIPPET}")],
            "notefolders_update"    => ["title" => Lang::get("WhoUpdatedNoteFolder", "{WHO} updated note folder {SNIPPET}")],
            "notefolders_delete"    => ["title" => Lang::get("WhoDeletedNoteFolder", "{WHO} deleted note folder {SNIPPET}")],

            // notes
            "notes_create"          => ["title" => Lang::get("WhoCreatedNote", "{WHO} created new note {SNIPPET}")],
            "notes_update"          => ["title" => Lang::get("WhoUpdatedNote", "{WHO} updated note {SNIPPET}")],
            "notes_delete"          => ["title" => Lang::get("WhoDeletedNote", "{WHO} deleted note {SNIPPET}")],

            // contacts
            "contacts_create"       => ["title" => Lang::get("WhoCreatedContact", "{WHO} created new contact {SNIPPET}")],
            "contacts_update"       => ["title" => Lang::get("WhoUpdatedContact", "{WHO} updated contact {SNIPPET}")],
            "contacts_delete"       => ["title" => Lang::get("WhoDeletedContact", "{WHO} deleted contact {SNIPPET}")],

            // translations
            "translations_create"   => ["title" => Lang::get("WhoCreatedTranslation", "{WHO} created new translation {SNIPPET}")],
            "translations_update"   => ["title" => Lang::get("WhoUpdatedTranslation", "{WHO} translated {SNIPPET}")],
            "translations_delete"   => ["title" => Lang::get("WhoDeletedTranslation", "{WHO} deleted translation {SNIPPET}")],

            // documents
            "documents_create"      => ["title" => Lang::get("WhoCreatedDoc", "{WHO} created new document {FOLDER_TITLE}")],
            "documents_update"      => ["title" => Lang::get("WhoUpdatedDoc", "{WHO} updated document {FOLDER_TITLE}")],
            "documents_delete"      => ["title" => Lang::get("WhoDeletedDoc", "{WHO} deleted document {FOLDER_TITLE}")],
            "documents_file_upload" => ["title" => Lang::get("WhoUploadedFile", "{WHO} uploaded {FILE} to {FOLDER_TITLE}")],
            "documents_file_update" => ["title" => Lang::get("WhoUpdatedDocFile", "{WHO} updated {FILE}")],
            "documents_file_delete" => ["title" => Lang::get("WhoDeletedDocFile", "{WHO} deleted {FILE}")],

            // permissions
            "permissions_update"    => ["title" => Lang::get("WhoUpdatedWhosePermission", "{WHO} updated {WHOSE} permission")],

            // transactions
            "transactions_create"   => ["title" => Lang::get("WhoCreatedTransaction", "{WHO} created new transaction {SNIPPET}")],
            "transactions_update"   => ["title" => Lang::get("WhoUpdatedTransaction", "{WHO} updated transaction {SNIPPET}")],
            "transactions_delete"   => ["title" => Lang::get("WhoDeletedTransaction", "{WHO} deleted transaction {SNIPPET}")],

            // deliveries
            "deliveries_create"     => ["title" => Lang::get("WhoCreatedDelivery", "{WHO} created new delivery {SNIPPET}")],
            "deliveries_update"     => ["title" => Lang::get("WhoUpdatedDelivery", "{WHO} updated delivery {SNIPPET}")],
            "deliveries_delete"     => ["title" => Lang::get("WhoDeletedDelivery", "{WHO} deleted delivery {SNIPPET}")],

            // timerecords
            "timerecords_create"     => ["title" => Lang::get("WhoCreatedTimeRecord", "{WHO} created new time record {SNIPPET}")],
            "timerecords_update"     => ["title" => Lang::get("WhoUpdatedTimeRecord", "{WHO} updated time record {SNIPPET}")],
            "timerecords_delete"     => ["title" => Lang::get("WhoDeletedTimeRecord", "{WHO} deleted time record {SNIPPET}")],

            // invoices
            "invoices_create"     => ["title" => Lang::get("WhoCreatedInvoice", "{WHO} created new invoice {SNIPPET}")],
            "invoices_update"     => ["title" => Lang::get("WhoUpdatedInvoice", "{WHO} updated invoice {SNIPPET}")],
            "invoices_delete"     => ["title" => Lang::get("WhoDeletedInvoice", "{WHO} deleted invoice {SNIPPET}")],
        ];
    }

    public function typeList()
    {
        $typeList = [
            "auth"         => Lang::get("Auth"),
            "calendar"     => Lang::get("Calendar"),
            "parameters"   => Lang::get("Parameters"),
            "lessons"      => Lang::get("Lessons"),
            "users"        => Lang::get("Users"),
            "cases"        => Lang::get("Cases"),
            "partners"     => Lang::get("Partners"),
            "notefolders"  => Lang::get("NoteFolders"),
            "notes"        => Lang::get("Notes"),
            "contacts"     => Lang::get("Contacts"),
            "translations" => Lang::get("Translations"),
            "documents"    => Lang::get("Documents"),
            "permissions"  => Lang::get("Permissions"),
            "transactions" => Lang::get("Transactions"),
            "deliveries"   => Lang::get("Deliveries"),
            "timerecords"  => Lang::get("TimeRecord"),
            "invoices"     => Lang::get("Invoices"),
        ];

        $crm_id = Auth::getData()->crm_type;
        $list = [];

        if ($crm_id == 2) {
            $denyList = [
                "deliveries",
                "timerecords"
            ];
            foreach ($typeList as $key => $title) {
                if(!in_array($key, $denyList)){
                    $list[] = [
                        'text'  => $title,
                        'value' => $key,
                    ];
                }
            }
        } else if ($crm_id == 3) {
            $denyList = [
                "cases",
                "lessons",
                "invoices",
            ];
            foreach ($typeList as $key => $title) {
                if(!in_array($key, $denyList)){
                    $list[] = [
                        'text'  => $title,
                        'value' => $key,
                    ];
                }
            }
        } else {
            foreach ($typeList as $key => $title) {
                $list[] = [
                    'text'  => $title,
                    'value' => $key,
                ];
            }
        }

        return $list;
    }

    public static function getSource()
    {
        return "activities";
    }

    public static function getById($id)
    {
        return self::findFirst([
            [
                "id" => (int) $id,
            ],
        ]);
    }
    public static function log($params)
    {
        $changes = [];
        if (substr($params["operation"], -6) == "update") {
            foreach ($params["newObject"] as $key => $value) {
                if (!in_array($key, ["updated_at"]) && $value != $params["oldObject"]->{$key}) {
                    $changes[$key] = [
                        'from' => $params["oldObject"]->{$key},
                        'to'   => $value,
                    ];
                }
            }
            if (count($changes) > 0) {
                $insert = true;
            }
        } else {
            $insert = true;
        }
        if ($insert) {
            $insertId = self::insert([
                "user_id"     => (int) $params["user_id"],
                "section"     => (string) $params["section"],
                "operation"   => (string) $params["operation"],
                "values"      => (array) $params["values"],
                "changes"     => (array) $changes,
                "status"      => (int) $params["status"],
                "is_deleted"  => 0,
                "is_notified" => 0,
                "created_at"  => self::getDate(),
            ]);
            if ($insertId) {
                return $insertId;
            }
        }
        return false;
    }
}
