<?php

namespace Sonicfoundry;

class SiteProperties {
    function __construct($json = null)
    {
        if(!is_null($json))
        {
            $this->Folders = $json->{'Folders@odata.navigationLinkUrl'};

            $this->ApiVersion = $json->ApiVersion;
            $this->ApiPublishedDate = $json->ApiPublishedDate;
            $this->SiteName = $json->SiteName;
            $this->SiteDescription = $json->SiteDescription;
            $this->SiteVersion = $json->SiteVersion;
            $this->SiteOwner = $json->SiteOwner;
            $this->SiteOwnerContact = $json->SiteOwnerContact;
            $this->SiteOwnerEmail = $json->SiteOwnerEmail;
            $this->SiteRootUrl = $json->SiteRootUrl;
            $this->ServiceRootUrl = $json->ServiceRootUrl;
            $this->LoggedInUserName = $json->LoggedInUserName;
            $this->RootFolderId = $json->RootFolderId;
        }
    }
    // Navigation Property
    public $Folders;
    // 'Normal' properties
    public $ApiVersion;
    public $ApiPublishedDate;
    public $SiteName;
    public $SiteDescription;
    public $SiteVersion;
    public $SiteOwner;
    public $SiteOwnerContact;
    public $SiteOwnerEmail;
    public $SiteRootUrl;
    public $ServiceRootUrl;
    public $LoggedInUserName;
    public $RootFolderId;

}
