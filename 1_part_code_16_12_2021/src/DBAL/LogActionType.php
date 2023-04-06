<?php


namespace App\DBAL;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class LogActionType extends Type
{
    const TYPE_NAME = "log_action_type";

    /** @var string Вход в приложение (1) */
    const ACCOUNT_AUTHORIZATION = "account_authorization";

    /** @var string Выход пользователя (2) */
    const ACCOUNT_LOGOUT = "account_logout";

    /** @var string Обновление токена (3) */
    const ACCOUNT_REFRESHTOKEN = "account_refreshtoken";

    /** @var string Просмотр профиля (4) */
    const ACCOUNT_GETPROFILE = "account_getprofile";

    /** @var string Обновление профиля (5) */
    const ACCOUNT_UPDATEPROFILE = "account_updateprofile";

    /** @var string Установка статуса оператора (6) */
    const ACCOUNT_SETSTATUS = "account_setstatus";

    const CALL_GETCALLS = "call_getcalls";

    const CONFIGURATIONS_GETATECONFIGURATIONS = "configurations_getateconfigurations";

    const CONFIGURATIONS_PUTATECONFIGURATIONS = "configurations_putateconfigurations";

    const CONTACT_GETCONTACTS = "contact_getcontacts";

    const CONTACT_POSTCONTACTS = "contact_postcontacts";

    const CONTACT_PATCHCONTACTS = "contact_patchcontacts";

    const CONTACT_SETDEFAULTPHONENUMBER = "contact_setdefaultphonenumber";

    const CONTACT_GETBYID = "contact_getbyid";

    const CONTACT_GETBYPHONENUMBER = "contact_getbyphonenumber";

    const CONTACT_DELETE = "contact_delete";

    /** @var string Передача контакта */
    const CONTACT_TRANSFER = "contact_transfer";

    const CONTACTEXPORT_EXPORTCONTACTS = "contactexport_exportcontacts";

    const CONTACTHISTORY_GETHISTORY = "contacthistory_gethistory";

    const CONTACTHISTORY_GETHISTORYBYID = "contacthistory_gethistorybyid";

    const CONTACTHISTORY_POSTHISTORY = "contacthistory_posthistory";

    const CONTACTHISTORY_PATCHHISTORY = "contacthistory_patchhistory";

    const CONTACTHISTORY_GETAUDIORECORD = "contacthistory_getaudiorecord";

    const CONTACTHISTORY_GETAUDIOFILE = "contacthistory_getaudiofile";

    const CONTACTIMPORT_IMPORTCONTACTS = "contactimport_importcontacts";

    const CONTACTTAG_GETTAGS = "contacttag_gettags";

    const CONTACTTAG_CREATETAG = "contacttag_createtag";

    const CONTACTTAG_SETTAGS = "contacttag_settags";

    const DATABASE_GETCOUNTRYCODES = "database_getcountrycodes";

    const DATABASE_GETALLSTATUSES = "database_getallstatuses";

    const GROUP_FIND = "group_find";

    const GROUP_CREATE = "group_create";

    const GROUP_EDIT = "group_edit";

    const GROUP_DELETE = "group_delete";

    const GROUP_GETBYID = "group_getbyid";

    const LEAD_GETLEADS = "lead_getleads";

    const LEAD_GETNEXT = "lead_getnext";

    const ORGANIZATION_FIND = "organization_find";

    const ORGANIZATION_MY = "organization_my";

    const ORGANIZATION_GETBYID = "organization_getbyid";

    const ORGANIZATION_CREATE = "organization_create";

    const ORGANIZATION_PUT = "organization_put";

    const ORGANIZATION_DELETE = "organization_delete";

    const ORGANIZATION_GETTAGS = "organization_gettags";

    const ORGANIZATION_GENERATEPERSONALACCESSTOKEN = "organization_generatepersonalaccesstoken";

    const PROJECT_FINDPROJECTS = "project_findprojects";

    const PROJECT_GETBYID = "project_getbyid";

    const PROJECT_MYPROJECT = "project_myproject";

    const PROJECT_CREATE = "project_create";

    const PROJECT_EDIT = "project_edit";

    const PROJECT_DELETE = "project_delete";

    const PROJECT_ADD_MEMBERS = "project_add_members";

    const PROJECT_ACTIVATE = "project_activate";

    const PROJECT_INACTIVE = "project_inactive";

    const ROLE_FINDROLES = "role_findroles";

    const ROLE_GETBYID = "role_getbyid";

    const ROLE_CREATE = "role_create";

    const ROLE_EDIT = "role_edit";

    const ROLE_DELETE = "role_delete";

    const STATISTICS_PIE = "statistics_pie";

    const STATISTICS_GETHISTORY = "statistics_gethistory";

    const STATISTICS_CALLCOUNT = "statistics_callcount";

    const STATISTICS_STATSACTIVITY = "statistics_statsactivity";

    const STATISTICS_UNAUTHORIZEDBREAKS = "statistics_unauthorizedbreaks";

    const STATISTICS_TOTALCALLS = "statistics_totalcalls";

    const STATUS_GETALLSTATUSES = "status_getallstatuses";

    const STATUS_GETBYID = "status_getbyid";

    const SYSTEM_ACTIVITY = "system_activity";

    const TASK_GETTASKS = "task_gettasks";

    const TASK_GETTASKSCOUNT = "task_gettaskscount";

    const TASK_GETTASKBYID = "task_gettaskbyid";

    const TASK_DELETE = "task_delete";

    const TASK_EDIT = "task_edit";

    const TASK_CREATE = "task_create";

    const TASK_SETSTATE = "task_setstate";

    const USER_GETUSERS = "user_getusers";

    const USER_USERCREATE = "user_usercreate";

    const USER_USEREDIT = "user_useredit";

    const USER_DELETE = "user_delete";

    const USER_GETBYID = "user_getbyid";

    const USER_SETPROJECT = "user_setproject";

    const USERSCHEDULE_GETSCHEDULE = "userschedule_getschedule";

    const USERSCHEDULE_SETSCHEDULE = "userschedule_setschedule";

    /**
     * @return string[]
     */
    public static function toArray(): array
    {
        return [
            self::ACCOUNT_AUTHORIZATION,
            self::ACCOUNT_LOGOUT,
            self::ACCOUNT_REFRESHTOKEN,
            self::ACCOUNT_GETPROFILE,
            self::ACCOUNT_UPDATEPROFILE,
            self::ACCOUNT_SETSTATUS,
            self::CALL_GETCALLS,
            self::CONFIGURATIONS_GETATECONFIGURATIONS,
            self::CONFIGURATIONS_PUTATECONFIGURATIONS,
            self::CONTACT_GETCONTACTS,
            self::CONTACT_POSTCONTACTS,
            self::CONTACT_PATCHCONTACTS,
            self::CONTACT_SETDEFAULTPHONENUMBER,
            self::CONTACT_GETBYID,
            self::CONTACT_GETBYPHONENUMBER,
            self::CONTACT_DELETE,
            self::CONTACT_TRANSFER,
            self::CONTACTEXPORT_EXPORTCONTACTS,
            self::CONTACTHISTORY_GETHISTORY,
            self::CONTACTHISTORY_GETHISTORYBYID,
            self::CONTACTHISTORY_POSTHISTORY,
            self::CONTACTHISTORY_PATCHHISTORY,
            self::CONTACTHISTORY_GETAUDIORECORD,
            self::CONTACTHISTORY_GETAUDIOFILE,
            self::CONTACTIMPORT_IMPORTCONTACTS,
            self::CONTACTTAG_GETTAGS,
            self::CONTACTTAG_CREATETAG,
            self::CONTACTTAG_SETTAGS,
            self::DATABASE_GETCOUNTRYCODES,
            self::DATABASE_GETALLSTATUSES,
            self::GROUP_FIND,
            self::GROUP_CREATE,
            self::GROUP_EDIT,
            self::GROUP_DELETE,
            self::GROUP_GETBYID,
            self::LEAD_GETLEADS,
            self::LEAD_GETNEXT,
            self::ORGANIZATION_FIND,
            self::ORGANIZATION_MY,
            self::ORGANIZATION_GETBYID,
            self::ORGANIZATION_CREATE,
            self::ORGANIZATION_PUT,
            self::ORGANIZATION_DELETE,
            self::ORGANIZATION_GETTAGS,
            self::ORGANIZATION_GENERATEPERSONALACCESSTOKEN,
            self::PROJECT_FINDPROJECTS,
            self::PROJECT_GETBYID,
            self::PROJECT_MYPROJECT,
            self::PROJECT_CREATE,
            self::PROJECT_EDIT,
            self::PROJECT_DELETE,
            self::PROJECT_ADD_MEMBERS,
            self::PROJECT_ACTIVATE,
            self::PROJECT_INACTIVE,
            self::ROLE_FINDROLES,
            self::ROLE_GETBYID,
            self::ROLE_CREATE,
            self::ROLE_EDIT,
            self::ROLE_DELETE,
            self::STATISTICS_PIE,
            self::STATISTICS_GETHISTORY,
            self::STATISTICS_CALLCOUNT,
            self::STATISTICS_STATSACTIVITY,
            self::STATISTICS_UNAUTHORIZEDBREAKS,
            self::STATISTICS_TOTALCALLS,
            self::STATUS_GETALLSTATUSES,
            self::STATUS_GETBYID,
            self::SYSTEM_ACTIVITY,
            self::TASK_GETTASKS,
            self::TASK_GETTASKSCOUNT,
            self::TASK_GETTASKBYID,
            self::TASK_DELETE,
            self::TASK_EDIT,
            self::TASK_CREATE,
            self::TASK_SETSTATE,
            self::USER_GETUSERS,
            self::USER_USERCREATE,
            self::USER_USEREDIT,
            self::USER_DELETE,
            self::USER_GETBYID,
            self::USER_SETPROJECT,
            self::USERSCHEDULE_GETSCHEDULE,
            self::USERSCHEDULE_SETSCHEDULE
        ];
    }

    /**
     * @param array            $column
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        return "TINYINT(1)";
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        switch ($value) {
            case 1:
                return self::ACCOUNT_AUTHORIZATION;
            case 2:
                return self::ACCOUNT_LOGOUT;
            case 3:
                return self::ACCOUNT_REFRESHTOKEN;
            case 4:
                return self::ACCOUNT_GETPROFILE;
            case 5:
                return self::ACCOUNT_UPDATEPROFILE;
            case 6:
                return self::ACCOUNT_SETSTATUS;
            case 7:
                return self::CALL_GETCALLS;
            case 8:
                return self::CONFIGURATIONS_GETATECONFIGURATIONS;
            case 9:
                return self::CONFIGURATIONS_PUTATECONFIGURATIONS;
            case 10:
                return self::CONTACT_GETCONTACTS;
            case 11:
                return self::CONTACT_POSTCONTACTS;
            case 12:
                return self::CONTACT_PATCHCONTACTS;
            case 13:
                return self::CONTACT_SETDEFAULTPHONENUMBER;
            case 14:
                return self::CONTACT_GETBYID;
            case 15:
                return self::CONTACT_GETBYPHONENUMBER;
            case 16:
                return self::CONTACT_DELETE;
            case 17:
                return self::CONTACT_TRANSFER;
            case 18:
                return self::CONTACTEXPORT_EXPORTCONTACTS;
            case 19:
                return self::CONTACTHISTORY_GETHISTORY;
            case 20:
                return self::CONTACTHISTORY_GETHISTORYBYID;
            case 21:
                return self::CONTACTHISTORY_POSTHISTORY;
            case 22:
                return self::CONTACTHISTORY_PATCHHISTORY;
            case 23:
                return self::CONTACTHISTORY_GETAUDIORECORD;
            case 24:
                return self::CONTACTHISTORY_GETAUDIOFILE;
            case 25:
                return self::CONTACTIMPORT_IMPORTCONTACTS;
            case 26:
                return self::CONTACTTAG_GETTAGS;
            case 27:
                return self::CONTACTTAG_CREATETAG;
            case 28:
                return self::CONTACTTAG_SETTAGS;
            case 29:
                return self::DATABASE_GETCOUNTRYCODES;
            case 30:
                return self::DATABASE_GETALLSTATUSES;
            case 31:
                return self::GROUP_FIND;
            case 32:
                return self::GROUP_CREATE;
            case 33:
                return self::GROUP_EDIT;
            case 34:
                return self::GROUP_DELETE;
            case 35:
                return self::GROUP_GETBYID;
            case 36:
                return self::LEAD_GETLEADS;
            case 37:
                return self::LEAD_GETNEXT;
            case 38:
                return self::ORGANIZATION_FIND;
            case 39:
                return self::ORGANIZATION_MY;
            case 40:
                return self::ORGANIZATION_GETBYID;
            case 41:
                return self::ORGANIZATION_CREATE;
            case 42:
                return self::ORGANIZATION_PUT;
            case 43:
                return self::ORGANIZATION_DELETE;
            case 44:
                return self::ORGANIZATION_GETTAGS;
            case 45:
                return self::ORGANIZATION_GENERATEPERSONALACCESSTOKEN;
            case 46:
                return self::PROJECT_FINDPROJECTS;
            case 47:
                return self::PROJECT_GETBYID;
            case 48:
                return self::PROJECT_MYPROJECT;
            case 49:
                return self::PROJECT_CREATE;
            case 50:
                return self::PROJECT_EDIT;
            case 51:
                return self::PROJECT_DELETE;
            case 52:
                return self::PROJECT_ACTIVATE;
            case 53:
                return self::PROJECT_INACTIVE;
            case 54:
                return self::ROLE_FINDROLES;
            case 55:
                return self::ROLE_GETBYID;
            case 56:
                return self::ROLE_CREATE;
            case 57:
                return self::ROLE_EDIT;
            case 58:
                return self::ROLE_DELETE;
            case 59:
                return self::STATISTICS_PIE;
            case 60:
                return self::STATISTICS_GETHISTORY;
            case 61:
                return self::STATISTICS_CALLCOUNT;
            case 62:
                return self::STATISTICS_STATSACTIVITY;
            case 63:
                return self::STATISTICS_UNAUTHORIZEDBREAKS;
            case 64:
                return self::STATISTICS_TOTALCALLS;
            case 65:
                return self::STATUS_GETALLSTATUSES;
            case 66:
                return self::STATUS_GETBYID;
            case 67:
                return self::SYSTEM_ACTIVITY;
            case 68:
                return self::TASK_GETTASKS;
            case 69:
                return self::TASK_GETTASKSCOUNT;
            case 70:
                return self::TASK_GETTASKBYID;
            case 71:
                return self::TASK_DELETE;
            case 72:
                return self::TASK_EDIT;
            case 73:
                return self::TASK_CREATE;
            case 74:
                return self::TASK_SETSTATE;
            case 75:
                return self::USER_GETUSERS;
            case 76:
                return self::USER_USERCREATE;
            case 77:
                return self::USER_USEREDIT;
            case 78:
                return self::USER_DELETE;
            case 79:
                return self::USER_GETBYID;
            case 80:
                return self::USER_SETPROJECT;
            case 81:
                return self::USERSCHEDULE_GETSCHEDULE;
            case 82:
                return self::USERSCHEDULE_SETSCHEDULE;
            case 83:
                return self::PROJECT_ADD_MEMBERS;
            default:
                return null;
        }
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return self::convert($value);
    }

    /**
     * @param $value
     *
     * @return int|null
     */
    public static function convert($value)
    {
        switch ($value) {
            case self::ACCOUNT_AUTHORIZATION:
                return 1;
            case self::ACCOUNT_LOGOUT:
                return 2;
            case self::ACCOUNT_REFRESHTOKEN:
                return 3;
            case self::ACCOUNT_GETPROFILE:
                return 4;
            case self::ACCOUNT_UPDATEPROFILE:
                return 5;
            case self::ACCOUNT_SETSTATUS:
                return 6;
            case self::CALL_GETCALLS:
                return 7;
            case self::CONFIGURATIONS_GETATECONFIGURATIONS:
                return 8;
            case self::CONFIGURATIONS_PUTATECONFIGURATIONS:
                return 9;
            case self::CONTACT_GETCONTACTS:
                return 10;
            case self::CONTACT_POSTCONTACTS:
                return 11;
            case self::CONTACT_PATCHCONTACTS:
                return 12;
            case self::CONTACT_SETDEFAULTPHONENUMBER:
                return 13;
            case self::CONTACT_GETBYID:
                return 14;
            case self::CONTACT_GETBYPHONENUMBER:
                return 15;
            case self::CONTACT_DELETE:
                return 16;
            case self::CONTACT_TRANSFER:
                return 17;
            case self::CONTACTEXPORT_EXPORTCONTACTS:
                return 18;
            case self::CONTACTHISTORY_GETHISTORY:
                return 19;
            case self::CONTACTHISTORY_GETHISTORYBYID:
                return 20;
            case self::CONTACTHISTORY_POSTHISTORY:
                return 21;
            case self::CONTACTHISTORY_PATCHHISTORY:
                return 22;
            case self::CONTACTHISTORY_GETAUDIORECORD:
                return 23;
            case self::CONTACTHISTORY_GETAUDIOFILE:
                return 24;
            case self::CONTACTIMPORT_IMPORTCONTACTS:
                return 25;
            case self::CONTACTTAG_GETTAGS:
                return 26;
            case self::CONTACTTAG_CREATETAG:
                return 27;
            case self::CONTACTTAG_SETTAGS:
                return 28;
            case self::DATABASE_GETCOUNTRYCODES:
                return 29;
            case self::DATABASE_GETALLSTATUSES:
                return 30;
            case self::GROUP_FIND:
                return 31;
            case self::GROUP_CREATE:
                return 32;
            case self::GROUP_EDIT:
                return 33;
            case self::GROUP_DELETE:
                return 34;
            case self::GROUP_GETBYID:
                return 35;
            case self::LEAD_GETLEADS:
                return 36;
            case self::LEAD_GETNEXT:
                return 37;
            case self::ORGANIZATION_FIND:
                return 38;
            case self::ORGANIZATION_MY:
                return 39;
            case self::ORGANIZATION_GETBYID:
                return 40;
            case self::ORGANIZATION_CREATE:
                return 41;
            case self::ORGANIZATION_PUT:
                return 42;
            case self::ORGANIZATION_DELETE:
                return 43;
            case self::ORGANIZATION_GETTAGS:
                return 44;
            case self::ORGANIZATION_GENERATEPERSONALACCESSTOKEN:
                return 45;
            case self::PROJECT_FINDPROJECTS:
                return 46;
            case self::PROJECT_GETBYID:
                return 47;
            case self::PROJECT_MYPROJECT:
                return 48;
            case self::PROJECT_CREATE:
                return 49;
            case self::PROJECT_EDIT:
                return 50;
            case self::PROJECT_DELETE:
                return 51;
            case self::PROJECT_ACTIVATE:
                return 52;
            case self::PROJECT_INACTIVE:
                return 53;
            case self::ROLE_FINDROLES:
                return 54;
            case self::ROLE_GETBYID:
                return 55;
            case self::ROLE_CREATE:
                return 56;
            case self::ROLE_EDIT:
                return 57;
            case self::ROLE_DELETE:
                return 58;
            case self::STATISTICS_PIE:
                return 59;
            case self::STATISTICS_GETHISTORY:
                return 60;
            case self::STATISTICS_CALLCOUNT:
                return 61;
            case self::STATISTICS_STATSACTIVITY:
                return 62;
            case self::STATISTICS_UNAUTHORIZEDBREAKS:
                return 63;
            case self::STATISTICS_TOTALCALLS:
                return 64;
            case self::STATUS_GETALLSTATUSES:
                return 65;
            case self::STATUS_GETBYID:
                return 66;
            case self::SYSTEM_ACTIVITY:
                return 67;
            case self::TASK_GETTASKS:
                return 68;
            case self::TASK_GETTASKSCOUNT:
                return 69;
            case self::TASK_GETTASKBYID:
                return 70;
            case self::TASK_DELETE:
                return 71;
            case self::TASK_EDIT:
                return 72;
            case self::TASK_CREATE:
                return 73;
            case self::TASK_SETSTATE:
                return 74;
            case self::USER_GETUSERS:
                return 75;
            case self::USER_USERCREATE:
                return 76;
            case self::USER_USEREDIT:
                return 77;
            case self::USER_DELETE:
                return 78;
            case self::USER_GETBYID:
                return 79;
            case self::USER_SETPROJECT:
                return 80;
            case self::USERSCHEDULE_GETSCHEDULE:
                return 81;
            case self::USERSCHEDULE_SETSCHEDULE:
                return 82;
            case self::PROJECT_ADD_MEMBERS:
                return 83;
            default:
                return null;
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::TYPE_NAME;
    }

    /**
     * @param AbstractPlatform $platform
     *
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

}
