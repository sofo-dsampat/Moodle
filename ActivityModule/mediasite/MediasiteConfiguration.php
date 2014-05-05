<?php

namespace Sonicfoundry;

global $CFG;
require_once("$CFG->dirroot/mod/mediasite/Singleton.php");

class MediasiteConfiguration extends Singleton {
    private static $properties;
    private static $propertynames;
    private static $table = 'mediasite_sites';
    public function __get($name) {
        global $DB;
        if(is_null(self::$properties)) {
            self::$properties = $DB->get_columns(self::$table);
            self::$propertynames = array();
            foreach(self::$properties as $property) {
                self::$propertynames[] = $property->name;
            }
        }
        if(in_array($name, self::$propertynames)) {
            $record = $DB->get_record(self::$table, array(), $name, IGNORE_MULTIPLE);
            $this->$name = $record->$name;
            return $this->$name;
        } else {
            error("$name is not a valid configuration property");
        }
    }
    public function __set($name, $value) {
        global $DB;
        if(is_null(self::$properties)) {
            self::$properties = $DB->get_columns(self::$table);
            self::$propertynames = array();
            foreach(self::$properties as $property) {
                self::$propertynames[] = $property->name;
            }
        }
        if(in_array($name, self::$propertynames)) {
            $this->$name = $value;
            if($name !== 'id') {
                $record = $DB->get_record(self::$table, array(), "id,$name", IGNORE_MULTIPLE);
                if($record->id == $this->id && $record->$name != $value) {
                    $record->$name = $value;
                    $DB->update_record(self::$table, $record);
                }
            }
        } else {
            error("$name is not a valid configuration property");
        }
    }
}

global $MEDIASITE;
$MEDIASITE = MediasiteConfiguration::getInstance();