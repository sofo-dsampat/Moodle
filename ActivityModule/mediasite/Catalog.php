<?php

namespace Sonicfoundry;

class Catalog {
    function __construct($json = null)
    {
        if(!is_null($json))
        {
            $this->Id = $json->Id;
            $this->LinkedFolderId = $json->LinkedFolderId;
            $this->Name = $json->Name;
            $this->Description = $json->Description;
            $this->CatalogUrl = $json->CatalogUrl;
            $this->Recycled = $json->Recycled;
        }
    }
    public function DatabaseRecord() {
        $record = new \stdClass();
        $record->resourceid = $this->Id;
        $record->linkfolderid = $this->LinkedFolderId;
        $record->name = $this->Name;
        $record->description = $this->Description;
        $record->catalogurl = $this->CatalogUrl;
        $record->recycled = $this->Recycled;
        return $record;
    }
    public $Id;
    public $LinkedFolderId;
    public $Name;
    public $Description;
    public $CatalogUrl;
    public $Recycled;
}
