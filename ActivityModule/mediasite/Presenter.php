<?php

namespace Sonicfoundry;

global $CFG;

require_once("$CFG->dirroot/mod/mediasite/Utility.php");

class Presenter {
    function __construct($json = null)
    {
        if(!is_null($json))
        {
            $this->Id = $json->Id;
            $this->MediasiteId = $json->MediasiteId;
            $this->Prefix = $json->Prefix;
            $this->FirstName = $json->FirstName;
            $this->MiddleName = $json->MiddleName;
            $this->LastName = $json->LastName;
            $this->Suffix = $json->Suffix;
            $this->ImageUrl = $json->ImageUrl;
            $this->ImageName = $json->ImageName;
            $this->Email = $json->Email;
            $this->BioUrl = $json->BioUrl;
            $this->AdditionalInfo = $json->AdditionalInfo;
        }
    }
    public function DatabaseRecord() {
        $record = new \stdClass();
        $record->resourceid = $this->Id;
        $record->mediasiteid = $this->MediasiteId;
        $record->prefix = $this->Prefix;
        $record->firstname = $this->FirstName;
        $record->middlename = $this->MiddleName;
        $record->lastname = $this->LastName;
        $record->suffix = $this->Suffix;
        $record->imageurl = $this->ImageUrl;
        $record->imagename = $this->ImageName;
        $record->email = $this->Email;
        $record->biourl = $this->BioUrl;
        $record->additionalinfo = substr_unicode($this->AdditionalInfo, 0, 255);
        return $record;
    }
    public $Id;
    public $MediasiteId;
    public $Prefix;
    public $FirstName;
    public $MiddleName;
    public $LastName;
    public $Suffix;
    public $ImageUrl;
    public $ImageName;
    public $Email;
    public $BioUrl;
    public $AdditionalInfo;
} 