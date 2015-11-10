<?php

namespace IMC\Library\API;

use IMC\I_Conf;
use IMC\Library\API\Call\MailChimp_Error;
use IMC\Library\API\Call\MailChimp_Campaigns;
use IMC\Library\API\Call\MailChimp_Ecomm;
use IMC\Library\API\Call\MailChimp_Folders;
use IMC\Library\API\Call\MailChimp_Templates;
use IMC\Library\API\Call\MailChimp_Users;
use IMC\Library\API\Call\MailChimp_Helper;
use IMC\Library\API\Call\MailChimp_Mobile;
use IMC\Library\API\Call\MailChimp_Lists;
use IMC\Library\API\Call\MailChimp_Vip;
use IMC\Library\API\Call\MailChimp_Reports;
use IMC\Library\API\Call\MailChimp_Gallery;

I_Conf::include_file(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Exceptions.php');

class MailChimp {


    /**
     * Placeholder attribute for MailChimp_Folders class
     *
     * @var MailChimp_Folders
     * @access public
     */
    var $folders;


    /**
     * Placeholder attribute for MailChimp_Templates class
     *
     * @var MailChimp_Templates
     * @access public
     */
    var $templates;


    /**
     * Placeholder attribute for MailChimp_Users class
     *
     * @var MailChimp_Users
     * @access public
     */
    var $users;


    /**
     * Placeholder attribute for MailChimp_Helper class
     *
     * @var MailChimp_Helper
     * @access public
     */
    var $helper;


    /**
     * Placeholder attribute for MailChimp_Mobile class
     *
     * @var MailChimp_Mobile
     * @access public
     */
    var $mobile;


    /**
     * Placeholder attribute for MailChimp_Ecomm class
     *
     * @var MailChimp_Ecomm
     * @access public
     */
    var $ecomm;


    /**
     * Placeholder attribute for MailChimp_Neapolitan class
     *
     * @var MailChimp_Neapolitan
     * @access public
     */
    var $neapolitan;


    /**
     * Placeholder attribute for MailChimp_Lists class
     *
     * @var MailChimp_Lists
     * @access public
     */
    var $lists;


    /**
     * Placeholder attribute for MailChimp_Campaigns class
     *
     * @var MailChimp_Campaigns
     * @access public
     */
    var $campaigns;


    /**
     * Placeholder attribute for MailChimp_Vip class
     *
     * @var MailChimp_Vip
     * @access public
     */
    var $vip;


    /**
     * Placeholder attribute for MailChimp_Reports class
     *
     * @var MailChimp_Reports
     * @access public
     */
    var $reports;


    /**
     * Placeholder attribute for MailChimp_Gallery class
     *
     * @var MailChimp_Gallery
     * @access public
     */
    var $gallery;


    /**
     * the api key in use
     * 
     * @var  string
     */
    public $apikey;
    public $root = 'https://api.mailchimp.com/2.0';


    /**
     * whether debug mode is enabled
     * 
     * @var  bool
     */
    public $debug            = false;
    public static $error_map = array(
        "ValidationError" => "MailChimp_ValidationError",
        "ServerError_MethodUnknown" => "MailChimp_ServerError_MethodUnknown",
        "ServerError_InvalidParameters" => "MailChimp_ServerError_InvalidParameters",
        "Unknown_Exception" => "MailChimp_Unknown_Exception",
        "Request_TimedOut" => "MailChimp_Request_TimedOut",
        "Zend_Uri_Exception" => "MailChimp_Zend_Uri_Exception",
        "PDOException" => "MailChimp_PDOException",
        "Avesta_Db_Exception" => "MailChimp_Avesta_Db_Exception",
        "XML_RPC2_Exception" => "MailChimp_XML_RPC2_Exception",
        "XML_RPC2_FaultException" => "MailChimp_XML_RPC2_FaultException",
        "Too_Many_Connections" => "MailChimp_Too_Many_Connections",
        "Parse_Exception" => "MailChimp_Parse_Exception",
        "User_Unknown" => "MailChimp_User_Unknown",
        "User_Disabled" => "MailChimp_User_Disabled",
        "User_DoesNotExist" => "MailChimp_User_DoesNotExist",
        "User_NotApproved" => "MailChimp_User_NotApproved",
        "Invalid_ApiKey" => "MailChimp_Invalid_ApiKey",
        "User_UnderMaintenance" => "MailChimp_User_UnderMaintenance",
        "Invalid_AppKey" => "MailChimp_Invalid_AppKey",
        "Invalid_IP" => "MailChimp_Invalid_IP",
        "User_DoesExist" => "MailChimp_User_DoesExist",
        "User_InvalidRole" => "MailChimp_User_InvalidRole",
        "User_InvalidAction" => "MailChimp_User_InvalidAction",
        "User_MissingEmail" => "MailChimp_User_MissingEmail",
        "User_CannotSendCampaign" => "MailChimp_User_CannotSendCampaign",
        "User_MissingModuleOutbox" => "MailChimp_User_MissingModuleOutbox",
        "User_ModuleAlreadyPurchased" => "MailChimp_User_ModuleAlreadyPurchased",
        "User_ModuleNotPurchased" => "MailChimp_User_ModuleNotPurchased",
        "User_NotEnoughCredit" => "MailChimp_User_NotEnoughCredit",
        "MC_InvalidPayment" => "MailChimp_MC_InvalidPayment",
        "List_DoesNotExist" => "MailChimp_List_DoesNotExist",
        "List_InvalidInterestFieldType" => "MailChimp_List_InvalidInterestFieldType",
        "List_InvalidOption" => "MailChimp_List_InvalidOption",
        "List_InvalidUnsubMember" => "MailChimp_List_InvalidUnsubMember",
        "List_InvalidBounceMember" => "MailChimp_List_InvalidBounceMember",
        "List_AlreadySubscribed" => "MailChimp_List_AlreadySubscribed",
        "List_NotSubscribed" => "MailChimp_List_NotSubscribed",
        "List_InvalidImport" => "MailChimp_List_InvalidImport",
        "MC_PastedList_Duplicate" => "MailChimp_MC_PastedList_Duplicate",
        "MC_PastedList_InvalidImport" => "MailChimp_MC_PastedList_InvalidImport",
        "Email_AlreadySubscribed" => "MailChimp_Email_AlreadySubscribed",
        "Email_AlreadyUnsubscribed" => "MailChimp_Email_AlreadyUnsubscribed",
        "Email_NotExists" => "MailChimp_Email_NotExists",
        "Email_NotSubscribed" => "MailChimp_Email_NotSubscribed",
        "List_MergeFieldRequired" => "MailChimp_List_MergeFieldRequired",
        "List_CannotRemoveEmailMerge" => "MailChimp_List_CannotRemoveEmailMerge",
        "List_Merge_InvalidMergeID" => "MailChimp_List_Merge_InvalidMergeID",
        "List_TooManyMergeFields" => "MailChimp_List_TooManyMergeFields",
        "List_InvalidMergeField" => "MailChimp_List_InvalidMergeField",
        "List_InvalidInterestGroup" => "MailChimp_List_InvalidInterestGroup",
        "List_TooManyInterestGroups" => "MailChimp_List_TooManyInterestGroups",
        "Campaign_DoesNotExist" => "MailChimp_Campaign_DoesNotExist",
        "Campaign_StatsNotAvailable" => "MailChimp_Campaign_StatsNotAvailable",
        "Campaign_InvalidAbsplit" => "MailChimp_Campaign_InvalidAbsplit",
        "Campaign_InvalidContent" => "MailChimp_Campaign_InvalidContent",
        "Campaign_InvalidOption" => "MailChimp_Campaign_InvalidOption",
        "Campaign_InvalidStatus" => "MailChimp_Campaign_InvalidStatus",
        "Campaign_NotSaved" => "MailChimp_Campaign_NotSaved",
        "Campaign_InvalidSegment" => "MailChimp_Campaign_InvalidSegment",
        "Campaign_InvalidRss" => "MailChimp_Campaign_InvalidRss",
        "Campaign_InvalidAuto" => "MailChimp_Campaign_InvalidAuto",
        "MC_ContentImport_InvalidArchive" => "MailChimp_MC_ContentImport_InvalidArchive",
        "Campaign_BounceMissing" => "MailChimp_Campaign_BounceMissing",
        "Campaign_InvalidTemplate" => "MailChimp_Campaign_InvalidTemplate",
        "Invalid_EcommOrder" => "MailChimp_Invalid_EcommOrder",
        "Absplit_UnknownError" => "MailChimp_Absplit_UnknownError",
        "Absplit_UnknownSplitTest" => "MailChimp_Absplit_UnknownSplitTest",
        "Absplit_UnknownTestType" => "MailChimp_Absplit_UnknownTestType",
        "Absplit_UnknownWaitUnit" => "MailChimp_Absplit_UnknownWaitUnit",
        "Absplit_UnknownWinnerType" => "MailChimp_Absplit_UnknownWinnerType",
        "Absplit_WinnerNotSelected" => "MailChimp_Absplit_WinnerNotSelected",
        "Invalid_Analytics" => "MailChimp_Invalid_Analytics",
        "Invalid_DateTime" => "MailChimp_Invalid_DateTime",
        "Invalid_Email" => "MailChimp_Invalid_Email",
        "Invalid_SendType" => "MailChimp_Invalid_SendType",
        "Invalid_Template" => "MailChimp_Invalid_Template",
        "Invalid_TrackingOptions" => "MailChimp_Invalid_TrackingOptions",
        "Invalid_Options" => "MailChimp_Invalid_Options",
        "Invalid_Folder" => "MailChimp_Invalid_Folder",
        "Invalid_URL" => "MailChimp_Invalid_URL",
        "Module_Unknown" => "MailChimp_Module_Unknown",
        "MonthlyPlan_Unknown" => "MailChimp_MonthlyPlan_Unknown",
        "Order_TypeUnknown" => "MailChimp_Order_TypeUnknown",
        "Invalid_PagingLimit" => "MailChimp_Invalid_PagingLimit",
        "Invalid_PagingStart" => "MailChimp_Invalid_PagingStart",
        "Max_Size_Reached" => "MailChimp_Max_Size_Reached",
        "MC_SearchException" => "MailChimp_MC_SearchException"
    );


    public function __construct($apikey = null, $call = null, $opts = array()) {

        $this->debug = get_option(I_Conf::OPT_ENABLE_DEBUG_MODE);

        if (!$apikey) {
            throw new MailChimp_Error('MailChimp_Error', 'Missing Required MailChimp API key');
        }

        $this->apikey = $apikey;
        $dc           = "us1";

        if (strstr($this->apikey, "-")) {
            list($key, $dc) = explode("-", $this->apikey, 2);
            if (!$dc) {
                $dc = "us1";
            }
        }

        $this->root = str_replace('https://api', 'https://' . $dc . '.api', $this->root);
        $this->root = rtrim($this->root, '/') . '/';

        switch ($call) {
            case I_Conf::CALL_CAMPAIGNS:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Campaigns.php');
                $this->campaigns = new MailChimp_Campaigns($this);
                break;
            case I_Conf::CALL_EXCOMM:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Ecomm.php');
                $this->ecomm     = new MailChimp_Ecomm($this);
                break;
            case I_Conf::CALL_FOLDERS:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Folders.php');
                $this->folders   = new MailChimp_Folders($this);
                break;
            case I_Conf::CALL_TEMPLATES:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Templates.php');
                $this->templates = new MailChimp_Templates($this);
                break;
            case I_Conf::CALL_USERS:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Users.php');
                $this->users     = new MailChimp_Users($this);
                break;
            case I_Conf::CALL_HELPER:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Helper.php');
                $this->helper    = new MailChimp_Helper($this);
                break;
            case I_Conf::CALL_MOBILE:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Mobile.php');
                $this->mobile    = new MailChimp_Mobile($this);
                break;
            case I_Conf::CALL_LISTS:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Lists.php');
                $this->lists     = new MailChimp_Lists($this);
                break;
            case I_Conf::CALL_VIP:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Vip.php');
                $this->vip       = new MailChimp_Vip($this);
                break;
            case I_Conf::CALL_REPORTS:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Reports.php');
                $this->reports   = new MailChimp_Reports($this);
                break;
            case I_Conf::CALL_GALLERY:
                require_once(IMC_PLUGIN_PATH . I_Conf::API_PATH . 'call/Gallery.php');
                $this->gallery   = new MailChimp_Gallery($this);
                break;
        }


    }


    public function call($url, $params) {
        $params['apikey'] = $this->apikey;
        $params           = array('body' => $params);

        usleep(125000);

        $response = wp_remote_post($this->root . $url . '.json', $params);

        if (is_wp_error($response)) {
            $error = $response->get_error_message();
            throw new MailChimp_Error('MailChimp_HttpError', "API call to $url failed: {$error}");
        }

        $http_code = wp_remote_retrieve_response_code($response);

        $response_body = json_decode($response['body'], true);


        if (floor($http_code / 100) >= 4) {
            throw $this->castError($response_body);
        }

        return $response_body;


    }


    public function castError($result) {
        if ($result['status'] !== 'error' || !$result['name']) {
            throw new MailChimp_Error('MailChimp_Error', 'We received an unexpected error: ' . json_encode($result));
        }

        $type = (isset(self::$error_map[$result['name']])) ? self::$error_map[$result['name']] : 'MailChimp_Error';

        return new MailChimp_Error($type, $result['error'], $result['code']);


    }


}

