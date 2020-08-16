<?php
define("TIMEZONE", "Iceland");

define("SERVER_TOKEN", "EhzBN7cbmUHdkPAaQ3RGVX3f");

$env = isset($_POST["_env"]) ? $_POST["_env"] : $_GET["_env"];
//define("_ENV", $env == "dev" ? "dev" : "crm");

//define("MONGO_DB", $env == "dev" ? "dev" : "crm");
define("_ENV", "production");

define("MONGO_DB", "transport");
define("_MAIN_LANG_", "en");
define("_LANG_", "en");
define("_ROOT_", "");
define("_PANEL_ROOT_", "");
define("COMPANY_NAME", "CRM");
define("FILE_DIR", "/home/crm/backend/sites/crm/public/upload/");

define("EMAIL_DOMAIN", "http://sualcavab.az/msend.php");
define("DEFAULT_EMAIL", "info@besfly.com");

define("TIME_DIFF", 2*3600);

define("FILE_URL", "https://www.besfly.com");
define("CRM_URL", "http://crm.besfly.com");

const Events = [1, 2, 3];

const _MODULES = [
    "db",
    "auth",
    "crons",
    "crm",
    "businesses",
    "account",
    "deliveries",
    "statistics",
    "timerecords",
    "cases",
    "users",
    "activities",
    "partners",
    "chat",
    "calendar",
    "parameters",
    "lessons",
    "translations",
    "account",
    "notefolders",
    "notes",
    "files",
    "lessonusers",
    "documents",
    "contacts",
    "permissions",
    "transactions",
    "businessprofile",
    "lessonsplan",
    "invoices",
    "accesslogs",
    "socket",
    "vehicles",
    "uservehicles",
    "worktimeexceptions",
    "geoobjects",
    "objects",
    "alerts",
    "notifications",
    "data",
    "history",
    "trackingstatistics",
    "vehicledamage",
    "deliverydamages",
    "pallets",
    "coco",
];

const USER_TYPES = [
    "moderator",
    "employee",
    "user",
    "partner"
];