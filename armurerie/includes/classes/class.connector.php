<?php

/**
 * Class.connector.php
 **/

if(!defined('__ARMORY__')) {
    die('Direct access to this file not allowed!');
}

Class Connector {
    
    /** Armory database handler **/
    public $aDB = null;
    
    /** Character database hanlder **/
    public $cDB = null;
    
    /** Realm/accounts database handler **/
    public $rDB = null;
    
    /** Mangos/world database handler **/
    public $wDB = null;
    
    /** MySQL connection configs **/
    public $mysqlconfig;
    
    /** Armory configs **/
    public $armoryconfig;
    
    /** Current armory locale (ru_ru or en_gb) **/
    public $_locale;
    
    /** Locale (0 - en_gb, 2 - fr_fr, 3 - de_de, etc.)**/
    public $_loc;
    
    /** Links for multirealm info **/
    public $realmData;    
    public $connectionData;
    public $currentRealmInfo;
    
    /** Debug handler **/
    private $debugHandler;        
    
    /**
     * Initialize database handlers, debug handler, sets up sql/site configs
     * @category Main system functions
     * @example Connector::Connector()
     * @return bool
     **/
    public function Connector() {
        if(!@include('class.config.php')) {
            die('<b>Erreur</b>: unable to load configuration file!');
        }
        if(!@require_once('class.dbhandler.php')) {
            die('<b>Erreur</b>: unable to load database class!');
        }
        if(!@require_once('class.debug.php')) {
            die('<b>Erreur</b>: unable to load debug class!');
        }
        $this->mysqlconfig  = $ArmoryConfig['mysql'];
        $this->armoryconfig = $ArmoryConfig['settings'];
        $this->debugHandler = new ArmoryDebug(array('useDebug' => $this->armoryconfig['useDebug'], 'logLevel' => $this->armoryconfig['logLevel']));
        $this->realmData    = $ArmoryConfig['multiRealm'];
        $this->aDB = new ArmoryDatabaseHandler($this->mysqlconfig['host_armory'], $this->mysqlconfig['user_armory'], $this->mysqlconfig['pass_armory'], $this->mysqlconfig['name_armory'], $this->mysqlconfig['charset_armory'], $this->Log());
        $this->rDB = new ArmoryDatabaseHandler($this->mysqlconfig['host_realmd'], $this->mysqlconfig['user_realmd'], $this->mysqlconfig['pass_realmd'], $this->mysqlconfig['name_realmd'], $this->mysqlconfig['charset_realmd'], $this->Log());
        if(isset($_GET['r'])) {
            $realmName = urldecode($_GET['r']);
            $realm_info = $this->aDB->selectRow("SELECT `id`, `version` FROM `armory_realm_data` WHERE `name`='%s'", $realmName);
            if(isset($this->realmData[$realm_info['id']])) {
                $this->connectionData = $this->realmData[$realm_info['id']];
                $this->cDB = new ArmoryDatabaseHandler($this->connectionData['host_characters'], $this->connectionData['user_characters'], $this->connectionData['pass_characters'], $this->connectionData['name_characters'], $this->connectionData['charset_characters'], $this->Log());
                $this->currentRealmInfo = array('name' => $this->connectionData['name'], 'id' => $realm_info['id'], 'version' => $realm_info['version'], 'type' => $this->connectionData['type'], 'connected' => true);
                if(isset($this->connectionData['name_mangos'])) {
                    $this->wDB = new ArmoryDatabaseHandler($this->connectionData['host_mangos'], $this->connectionData['user_mangos'], $this->connectionData['pass_mangos'], $this->connectionData['name_mangos'], $this->connectionData['charset_mangos'], $this->Log());
                }
            }
        }
        $realm_info = $this->realmData[1];
        if($this->cDB == null) {
            $this->cDB = new ArmoryDatabaseHandler($realm_info['host_characters'], $realm_info['user_characters'], $realm_info['pass_characters'], $realm_info['name_characters'], $realm_info['charset_characters'], $this->Log());
        }
        if($this->wDB == null) {
            $this->wDB = new ArmoryDatabaseHandler($realm_info['host_mangos'], $realm_info['user_mangos'], $realm_info['pass_mangos'], $realm_info['name_mangos'], $realm_info['charset_mangos'], $this->Log());
        }
        if(!$this->currentRealmInfo) {
            $this->currentRealmInfo = array('name' => $this->realmData[1]['name'], 'id' => 1, 'version' => $this->armoryconfig['server_version'], 'type' => $this->realmData[1]['type'], 'connected' => true);
        }
        if(!$this->connectionData) {
            $this->connectionData = $this->realmData[1];
        }
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $user_locale = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
            if($user_locale && $http_locale = self::IsAllowedLocale($user_locale)) {
                $this->_locale = (isset($_SESSION['armoryLocale'])) ? $_SESSION['armoryLocale'] : $http_locale;
            }
        }
        if(!$this->_locale) {
            $this->_locale = (isset($_SESSION['armoryLocale'])) ? $_SESSION['armoryLocale'] : $this->armoryconfig['defaultLocale'];
        }
        switch($this->_locale) {
            case 'en_gb':
            case 'en_us':
                $this->_loc = 0;
                break;
            case 'fr_fr':
                $this->_loc = 2;
                break;
            case 'de_de':
                $this->_loc = 3;
                break;
            case 'es_es':
                $this->_loc = 6;
                break;
            case 'es_mx':
                $this->_loc = 7;
                break;
            case 'ru_ru':
                $this->_loc = 8;
                break;
        }
    }
    
    /**
     * Checks browser language from HTTP_ACCEPT_LANGUAGE
     * @category Main system functions
     * @example Connector::IsAllowedLocale('ru');
     * @return mixed
     **/
    private function IsAllowedLocale($locale) {
        switch($locale) {
            case 'de':
                return 'de_de';
                break;
            case 'en':
                return 'en_gb';
                break;
            case 'es':
                return 'es_es';
                break;
            case 'fr':
                return 'fr_fr';
                break;
            case 'ru':
                return 'ru_ru';
                break;
            default:
                return false;
                break;
        }
    }
    
    /**
     * Returns debug log handler
     **/
    public function Log() {
        return $this->debugHandler;
    }
}
?>