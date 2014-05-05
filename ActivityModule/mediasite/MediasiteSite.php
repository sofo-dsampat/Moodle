<?php

namespace Sonicfoundry;

global $CFG;
require_once("$CFG->dirroot/mod/mediasite/MediasiteConfiguration.php");

class MediasiteSite {
    private $id;
    private $sitename;
    private $endpoint;
    private $apikey;
    private $username;
    private $password;
    private $duration;
    private $restrictip;
    private $passthru;
    function __construct($record = null) {
        if(!is_null($record)) {
            if($record instanceof MediasiteSite) {
                $this->id = $record->id;
                $this->sitename = $record->sitename;
                $this->endpoint = $record->endpoint;
                $this->apikey = $record->apikey;
                $this->username = $record->username;
                $this->password = $record->password;
                $this->duration = $record->duration;
                $this->restrictip = $record->restrictip;
                $this->passthru = $record->passthru;
            } elseif($record instanceof \stdClass) {
                $this->id = $record->id;
                $this->sitename = $record->sitename;
                $this->endpoint = $record->endpoint;
                $this->apikey = $record->apikey;
                $this->username = $record->username;
                $this->password = $record->password;
                $this->duration = $record->duration;
                $this->restrictip = $record->restrictip;
                $this->passthru = $record->passthru;
            } elseif(is_numeric($record)) {
                global $DB;
                $record = $DB->get_record('mediasite_sites', array('id'=>$record));
                if($record) {
                    $this->id = $record->id;
                    $this->sitename = $record->sitename;
                    $this->endpoint = $record->endpoint;
                    $this->apikey = $record->apikey;
                    $this->username = $record->username;
                    $this->password = $record->password;
                    $this->duration = $record->duration;
                    $this->restrictip = $record->restrictip;
                    $this->passthru = $record->passthru;
                }
            }
        }
    }
    function set_config() {
        global $MEDIASITE;
        $MEDIASITE->id = $this->id;
        $MEDIASITE->sitename = $this->sitename;
        $MEDIASITE->endpoint = $this->endpoint;
        $MEDIASITE->username = $this->username;
        $MEDIASITE->password = $this->password;
        $MEDIASITE->duration = $this->duration;
        $MEDIASITE->restrictip = $this->restrictip;
        $MEDIASITE->passthru = $this->passthru;
        $MEDIASITE->apikey = $this->apikey;
    }
    // Makes the members of this instance the same as corresponding values in $CFG
    function synch_config() {
        global $MEDIASITE;
        $this->id = $MEDIASITE->id;
        $this->sitename = $MEDIASITE->sitename;
        $this->endpoint = $MEDIASITE->endpoint;
        $this->username = $MEDIASITE->username;
        $this->password = $MEDIASITE->password;
        $this->duration = $MEDIASITE->duration;
        $this->restrictip = $MEDIASITE->restrictip;
        $this->passthru = $MEDIASITE->passthru;
        $this->apikey = $MEDIASITE->apikey;
    }
    function update_database() {
        $record = new \stdClass();
        $record->id = $this->id;
        $record->sitename = $this->sitename;
        $record->endpoint = $this->endpoint;
        $record->apikey = $this->apikey;
        $record->username = $this->username;
        $record->password = $this->password;
        $record->duration = $this->duration;
        $record->restrictip = $this->restrictip;
        $record->passthru = $this->passthru;
        global $DB;
        $DB->update_record('mediasite_sites', $record);
    }
    function get_siteid() {
        return $this->id;
    }
    function set_sitename($value) {
        $this->sitename = $value;
    }
    function get_sitename() {
        return $this->sitename;
    }
    function set_endpoint($value) {
        $this->endpoint = $value;
    }
    function get_endpoint() {
        return $this->endpoint;
    }
    function set_apikey($value) {
        $this->apikey = $value;
    }
    function get_apikey() {
        return $this->apikey;
    }
    function set_username($value) {
        $this->username = $value;
    }
    function get_username() {
        return $this->username;
    }
    function set_password($value) {
        $this->password = $value;
    }
    function get_password() {
        return $this->password;
    }
    function set_duration($value) {
        $this->duration = $value;
    }
    function get_duration() {
        return $this->duration;
    }
    function set_restrictip($value) {
        $this->restrictip = $value;
    }
    function get_restrictip() {
        return $this->restrictip;
    }
    function set_passthru($value) {
        $this->passthru = $value;
    }
    function get_passthru() {
        return $this->passthru;
    }
    static function loadbyname($name) {
        global $DB;
        if($record = $DB->get_record('mediasite_sites', array('sitename'=>$name))) {
            $site = new MediasiteSite($record);
            return $site;
        } else {
            return FALSE;
        }
    }
}
