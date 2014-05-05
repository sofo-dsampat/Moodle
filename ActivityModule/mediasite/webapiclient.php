<?php
require_once("$CFG->dirroot/mod/mediasite/SiteProperties.php");
require_once("$CFG->dirroot/mod/mediasite/Catalog.php");
require_once("$CFG->dirroot/mod/mediasite/Presentation.php");
require_once("$CFG->dirroot/mod/mediasite/ThumbnailContent.php");
require_once("$CFG->dirroot/mod/mediasite/ApiKey.php");
require_once("$CFG->dirroot/mod/mediasite/Utility.php");
require_once("$CFG->dirroot/mod/mediasite/lib.php");

// Mediasite WebApi webservice client wrapper

define("MEDIASITE_WEBAPI_JSONACCEPT", "application/json");
define("MEDIASITE_WEBAPI_JSONCONTENTTYPE", "application/json");
define("MEDIASITE_WEBAPI_HOST", "Sonicfoundry");

class QueryOptions {
    public $includePresenters = FALSE;
    public $includeThumbnail = FALSE;
    public $includeSlides = FALSE;
}

class WebApiExternalAccessClient {
    // Open a cURL resource
    private $_ch;
    private $_rootUrl;
    private $_curlOptions;
    private $_authorization;
    private $_apiKey;
    private $_apiKeyId;
    private $_apiKeyHeader;
    private $_cookie;
    private $_closed = FALSE;
    private $_passthru = null;
    const VERSION = 'api/v1/';
    private function FindCookie($header)
    {
        if( ($cookiestart = strpos($header, "Set-Cookie:")) !== FALSE)
        {
            if( ($cookieend = strpos($header, PHP_EOL, $cookiestart)) !== FALSE)
            {
                $cookie = substr($header, $cookiestart, $cookieend - $cookiestart);
                if(preg_match('/MediasiteAuth=([^;]+)/', $cookie, $matches))
                {
                    return "MediasiteAuth=$matches[1]";
                }
            }
        }
        return FALSE;
    }
    private function AddOptions($option)
    {
        if($this->_curlOptions == null)
        {
            $this->_curlOptions = array();
        }
        if(is_array($option))
        {
            foreach($option as $key => $value)
            {
                $this->_curlOptions[$key] = $value;
            }
        }
        else
        {
            $this->_curlOptions[] = $option;
        }
    }
    private function GetOptions()
    {
        return $this->_curlOptions;
    }
    function __construct($serviceLocation, $userName, $password, $passthru = null, $proxy = '')
    {
        $this->Open($serviceLocation, $userName, $password, $passthru, $proxy);
    }
    function __destruct()
    {
        $this->Close();
    }
    function Open($serviceLocation, $userName, $password, $passthru, $proxy)
    {
        $this->_closed = FALSE;
        if(\Sonicfoundry\EndsWith($serviceLocation, "/"))
        {
            $this->_rootUrl = $serviceLocation.self::VERSION;
        }
        else
        {
            $this->_rootUrl = $serviceLocation.'/'.self::VERSION;
        }
        $this->_passthru = $passthru;
        if(is_null($this->_passthru)) {
            $this->_authorization = 'Authorization: '.'Basic '.base64_encode($userName . ':' . $password);
        } else {
            $this->_authorization = 'Authorization: '.'SfIdentTicket '.base64_encode($userName . ':' . $password . ':' . $passthru);
        }
        $this->_ch = curl_init();
        $this->AddOptions(array(CURLOPT_FAILONERROR => TRUE,
                                CURLOPT_FOLLOWLOCATION => TRUE,
                                CURLOPT_RETURNTRANSFER => TRUE,
                                CURLOPT_USERAGENT => "Mediasite Moodle Plugin"));
        if(!empty($proxy))
        {
            $this->AddOptions(array(CURLOPT_PROXY => $proxy));
        }
        //curl_setopt($this->_ch, CURLOPT_COOKIEJAR, "tmp/cookieFileName");
        //$tempFileName = tempnam(getcwd(), "ms_");
        global $CFG;
        if(isset($CFG->mediasite_apikey) && !is_null($CFG->mediasite_apikey) && !empty($CFG->mediasite_apikey))
        {
            $this->_apiKeyId = $CFG->mediasite_apikey;
        }
        else
        {
            $this->_apiKey = null;
            try {
                $this->_apiKey = $this->GetApiKeyByName('MoodlePlugin');
                $this->_apiKeyId = $this->_apiKey->Id;
            } catch (Exception $e) {
                // Ignore
            }
        }
        if(!is_null($this->_apiKeyId))
        {
            $CFG->mediasite_apikey = $this->_apiKeyId;
            $this->_apiKeyHeader = 'sfapikey: '.$this->_apiKeyId;
        }
        else
        {
            $this->_apiKeyHeader = 'sfapikey: ';
        }
    }
    function Close()
    {
        // Close the cURL resource
        if(!$this->_closed)
        {
            curl_close($this->_ch);
            $this->_authorization = '';
            $this->_apiKey = null;
            $this->_apiKeyHeader = '';
            $this->_curlOptions = null;
            $this->_ch = null;
            $this->_rootUrl = '';
            $this->_closed = TRUE;
        }
    }
    function Version($verbose=FALSE)
    {
        $siteProperties = QuerySiteProperties();
        return $siteProperties->SiteVersion;
    }
    function QuerySiteProperties($verbose=FALSE)
    {

        $url = $this->_rootUrl.'Home';
        $this->AddOptions(array(CURLOPT_HEADER => 0,
                                CURLOPT_VERBOSE => $verbose,
                                CURLOPT_URL => $url,
                                CURLOPT_HTTPGET => TRUE,
                                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT)));
        curl_setopt_array($this->_ch, $this->GetOptions());
        if( ! $result = curl_exec($this->_ch))
        {
            $errormsg =  'QuerySiteProperties error: ' . curl_error($this->_ch);
            throw new Exception($errormsg);
        }
        $json = json_decode($result);
        $response = new Sonicfoundry\SiteProperties();

        $response->Folders = $json->{'Folders@odata.navigationLinkUrl'};

        $response->ApiVersion = $json->ApiVersion;
        $response->ApiPublishedDate = $json->ApiPublishedDate;
        $response->SiteName = $json->SiteName;
        $response->SiteDescription = $json->SiteDescription;
        $response->SiteVersion = $json->SiteVersion;
        $response->SiteBuildNumber = $json->SiteBuildNumber;
        $response->SiteOwner = $json->SiteOwner;
        $response->SiteOwnerContact = $json->SiteOwnerContact;
        $response->SiteOwnerEmail = $json->SiteOwnerEmail;
        $response->SiteRootUrl = $json->SiteRootUrl;
        $response->ServiceRootUrl = $json->ServiceRootUrl;
        $response->ServerTime = $json->ServerTime;
        $response->LoggedInUserName = $json->LoggedInUserName;
        $response->RootFolderId = $json->RootFolderId;

        return $response;
    }
    function QueryCatalogShares($searchText = '', $verbose=FALSE)
    {
        $url = $this->_rootUrl.'Catalogs';
        if(is_numeric($searchText) || !empty($searchText))
        {
            $url .= $searchText;
        }
        $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                                CURLOPT_VERBOSE => $verbose,
                                CURLOPT_URL => $url,
                                CURLOPT_HTTPGET => TRUE,
                                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                             $this->_authorization,
                                                             $this->_apiKeyHeader)));
        $cookieFound = FALSE;
        $response = array();
        curl_setopt_array($this->_ch, $this->GetOptions());
        if( ! $result = curl_exec($this->_ch))
        {
            $errormsg =  'QueryCatalogShares error: ' . curl_error($this->_ch);
            throw new Exception($errormsg);
        }
        else
        {
            $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
            if(!$cookieFound)
            {
                $header = substr($result, 0, $header_size);
                $cookie = $this->FindCookie($header);
                if($cookie !== FALSE)
                {
                    $this->_cookie = $cookie;
                    $this->AddOptions(array(CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                                         $this->_apiKeyHeader),
                                            CURLOPT_COOKIE => $cookie));
                    $cookieFound = TRUE;
                }
            }
            $body = substr($result, $header_size);
            $json = json_decode($body);
            $continue = TRUE;
            do{
                foreach($json->value as $catalog)
                {
                    $catalogShare = new Sonicfoundry\Catalog($catalog);
                    $response[] = $catalogShare;
                }
                if(isset($json->{'odata.nextLink'}))
                {
                    $this->AddOptions(array(CURLOPT_URL => $json->{'odata.nextLink'}));
                    curl_setopt_array($this->_ch, $this->GetOptions());
                    if( ! $result = curl_exec($this->_ch))
                    {
                        $errormsg = 'Curl error: ' . curl_error($this->_ch).' - ';
                        $errormsg .=  $json->{'odata.nextLink'};
                        throw new Exception($errormsg);
                    }
                    else
                    {
                        $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                        if(!$cookieFound)
                        {
                            $header = substr($result, 0, $header_size);
                            $cookie = $this->FindCookie($header);
                            if($cookie !== FALSE)
                            {
                                $this->_cookie = $cookie;
                                $this->AddOptions(array(CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                                                     $this->_apiKeyHeader),
                                                        CURLOPT_COOKIE => $cookie));
                                $cookieFound = TRUE;
                            }
                        }
                        $body = substr($result, $header_size);
                        $json = json_decode($body);
                    }
                }
                else
                {
                    $continue = FALSE;
                }
            } while($continue);
        }
        return $response;
    }
    function QueryPresentations($searchText = '', QueryOptions $queryOptions=NULL, $verbose=FALSE)
    {
        $url = $this->_rootUrl.'Presentations';
        if(is_numeric($searchText) || !empty($searchText))
        {
            $url .= $searchText;
        }
        $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                                CURLOPT_VERBOSE => $verbose,
                                CURLOPT_URL => $url,
                                CURLOPT_HTTPGET => TRUE,
                                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                             $this->_authorization,
                                                             $this->_apiKeyHeader)));
        $cookieFound = FALSE;
        $response = array();
        curl_setopt_array($this->_ch, $this->GetOptions());
        if( ! $result = curl_exec($this->_ch))
        {
            $errormsg =  'QueryPresentations error: ' . curl_error($this->_ch);
            throw new Exception($errormsg);
        }
        else
        {
            $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
            if(!$cookieFound)
            {
                $header = substr($result, 0, $header_size);
                $cookie = $this->FindCookie($header);
                if($cookie !== FALSE)
                {
                    $this->_cookie = $cookie;
                    $this->AddOptions(array(CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                                         $this->_apiKeyHeader),
                                            CURLOPT_COOKIE => $cookie));
                    $cookieFound = TRUE;
                }
            }
            $body = substr($result, $header_size);
            $json = json_decode($body);
            $continue = TRUE;
            do{
                if (preg_match("/select=full/", $searchText)) {
                    foreach($json->value as $presentation)
                    {
                        $presentationRepresentation = new Sonicfoundry\Presentation($presentation);
                        if(!is_null($queryOptions) && $queryOptions->includeThumbnail) {
                            $this->AddOptions(array(CURLOPT_URL => $presentation->{'ThumbnailContent@odata.navigationLinkUrl'}));
                            curl_setopt_array($this->_ch, $this->GetOptions());
                            if( $result = curl_exec($this->_ch)) {
                                $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                                if(!$cookieFound)
                                {
                                    $header = substr($result, 0, $header_size);
                                    $cookie = $this->FindCookie($header);
                                    if($cookie !== FALSE)
                                    {
                                        $this->_cookie = $cookie;
                                        $this->AddOptions(array(CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                                                            $this->_apiKeyHeader),
                                                                CURLOPT_COOKIE => $cookie));
                                        $cookieFound = TRUE;
                                    }
                                }
                                $body = substr($result, $header_size);
                                $jsonthumbnail = json_decode($body);
                                foreach($jsonthumbnail->value as $content) {
                                    $thumbnailcontent = new Sonicfoundry\ThumbnailContent($content);
                                    $presentationRepresentation->AddThumbnail($thumbnailcontent);
                                }
                            }
                        }
                        $presentationRepresentation->set_cookie($this->_cookie);
                        $presentationRepresentation->Play = $presentation->{'#Play'}->target;
                        $response[] = $presentationRepresentation;
                    }
                } elseif(preg_match("/select=card/", $searchText)) {
                    foreach($json->value as $presentation)
                    {
                        $presentationRepresentation = new Sonicfoundry\CardPresentation($presentation);
                        if(!is_null($queryOptions) && $queryOptions->includeThumbnail) {
                            $this->AddOptions(array(CURLOPT_URL => $presentation->{'ThumbnailContent@odata.navigationLinkUrl'}));
                            curl_setopt_array($this->_ch, $this->GetOptions());
                            if( $result = curl_exec($this->_ch)) {
                                $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                                if(!$cookieFound)
                                {
                                    $header = substr($result, 0, $header_size);
                                    $cookie = $this->FindCookie($header);
                                    if($cookie !== FALSE)
                                    {
                                        $this->_cookie = $cookie;
                                        $this->AddOptions(array(CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                                                            $this->_apiKeyHeader),
                                                                CURLOPT_COOKIE => $cookie));
                                        $cookieFound = TRUE;
                                    }
                                }
                                $body = substr($result, $header_size);
                                $jsonthumbnail = json_decode($body);
                                foreach($jsonthumbnail->value as $content) {
                                    $thumbnailcontent = new Sonicfoundry\ThumbnailContent($content);
                                    $presentationRepresentation->AddThumbnail($thumbnailcontent);
                                }
                            }
                        }
                        $presentationRepresentation->set_cookie($this->_cookie);
                        $presentationRepresentation->Play = $presentation->{'#Play'}->target;
                        $response[] = $presentationRepresentation;
                    }
                } else {
                    foreach($json->value as $presentation)
                    {
                        $presentationRepresentation = new Sonicfoundry\DefaultPresentation($presentation);
                        if(is_null($queryOptions) && $queryOptions->includeThumbnail) {
                            $this->AddOptions(array(CURLOPT_URL => $presentation->{'ThumbnailContent@odata.navigationLinkUrl'}));
                            curl_setopt_array($this->_ch, $this->GetOptions());
                            if( $result = curl_exec($this->_ch)) {
                                $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                                if(!$cookieFound)
                                {
                                    $header = substr($result, 0, $header_size);
                                    $cookie = $this->FindCookie($header);
                                    if($cookie !== FALSE)
                                    {
                                        $this->_cookie = $cookie;
                                        $this->AddOptions(array(CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                                                            $this->_apiKeyHeader),
                                                                CURLOPT_COOKIE => $cookie));
                                        $cookieFound = TRUE;
                                    }
                                }
                                $body = substr($result, $header_size);
                                $jsonthumbnail = json_decode($body);
                                foreach($jsonthumbnail->value as $content) {
                                    $thumbnailcontent = new Sonicfoundry\ThumbnailContent($content);
                                    $presentationRepresentation->AddThumbnail($thumbnailcontent);
                                }
                            }
                        }
                        $presentationRepresentation->set_cookie($this->_cookie);
                        $presentationRepresentation->Play = $presentation->{'#Play'}->target;
                        $response[] = $presentationRepresentation;
                    }
                }
                if(isset($json->{'odata.nextLink'}))
                {
                    $this->AddOptions(array(CURLOPT_URL => $json->{'odata.nextLink'}));
                    curl_setopt_array($this->_ch, $this->GetOptions());
                    if( ! $result = curl_exec($this->_ch))
                    {
                        $errormsg = 'Curl error: ' . curl_error($this->_ch).' - ';
                        $errormsg .=  $json->{'odata.nextLink'};
                        throw new Exception($errormsg);
                    }
                    else
                    {
                        $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                        if(!$cookieFound)
                        {
                            $header = substr($result, 0, $header_size);
                            $cookie = $this->FindCookie($header);
                            if($cookie !== FALSE)
                            {
                                $this->_cookie = $cookie;
                                $this->AddOptions(array(CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                                                     $this->_apiKeyHeader),
                                                        CURLOPT_COOKIE => $cookie));
                                $cookieFound = TRUE;
                            }
                        }
                        $body = substr($result, $header_size);
                        $json = json_decode($body);
                    }
                }
                else
                {
                    $continue = FALSE;
                }
            } while($continue);
        }

        return $response;

    }
    function GetPresentersForPresentation($resources, $verbose=FALSE) {
        if(empty($resources))
        {
            // Empty/null resource id
            throw new Exception('Empty/null presentation id');
        }
        if($this->_cookie != null && !empty($this->_cookie))
        {
            $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                CURLOPT_VERBOSE => $verbose,
                CURLOPT_HTTPGET => TRUE,
                CURLOPT_COOKIE => $this->_cookie,
                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                    $this->_apiKeyHeader)));
        }
        else
        {
            $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                CURLOPT_VERBOSE => $verbose,
                CURLOPT_HTTPGET => TRUE,
                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                    $this->_authorization,
                    $this->_apiKeyHeader)));
        }
        $response = array();
        if(is_array($resources))
        {
            $mh = curl_multi_init();
            $handles = array();
            foreach($resources as $resource)
            {
                $url = $this->_rootUrl.'Presentations(\''.$resource.'\')Presenters';
                $this->AddOptions(array(CURLOPT_URL => $url));
                $handles[$url] = curl_init($url);
                curl_setopt_array($handles[$url], $this->GetOptions());
                curl_multi_add_handle($mh, $handles[$url]);
            }
            $running = null;

            do {
                curl_multi_exec($mh, $running);
                usleep(100000);
            } while ($running > 0);

            foreach ($handles as $key => $value)
            {
                if(curl_errno($value))
                {
                    $errormsg =  "GetPresentersForPresentation ($key) error: " . curl_error($value);
                    $response[] = $errormsg;
                }
                else
                {
                    $header_size = curl_getinfo($value, CURLINFO_HEADER_SIZE);
                    $result = curl_multi_getcontent($value);
                    $header = substr($result, 0, $header_size);
                    $cookie = $this->FindCookie($header);
                    if($cookie !== FALSE)
                    {
                        $this->_cookie = $cookie;
                    }
                    $body = substr($result, $header_size);
                    $json = json_decode($body);
                    foreach($json->value as $content) {
                        $presenterRepresentation = new Sonicfoundry\Presenter($content);
                        $response[] = $presenterRepresentation;
                    }
                }

                curl_multi_remove_handle($mh, $value);
                curl_close($value);
            }
            return $response;
        }
        else
        {
            $url = $this->_rootUrl.'Presentations(\''.$resources.'\')Presenters';
            $this->AddOptions(array(CURLOPT_URL => $url));
            curl_setopt_array($this->_ch, $this->GetOptions());
            if( ! $result = curl_exec($this->_ch))
            {
                $errormsg =  'GetPresentersForPresentation error: ' . curl_error($this->_ch);
                throw new Exception($errormsg);
            }
            else
            {
                $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                $header = substr($result, 0, $header_size);
                $cookie = $this->FindCookie($header);
                if($cookie !== FALSE)
                {
                    $this->_cookie = $cookie;
                }
                $body = substr($result, $header_size);
                $json = json_decode($body);
                foreach($json->value as $content) {
                    $presenterRepresentation = new Sonicfoundry\Presenter($content);
                    $response[] = $presenterRepresentation;
                }
                return $response;
            }
        }
    }
    function GetThumbnailContentForPresentation($resources, $verbose=FALSE) {
        if(empty($resources))
        {
            // Empty/null resource id
            throw new Exception('Empty/null presentation id');
        }
        if($this->_cookie != null && !empty($this->_cookie))
        {
            $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                CURLOPT_VERBOSE => $verbose,
                CURLOPT_HTTPGET => TRUE,
                CURLOPT_COOKIE => $this->_cookie,
                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                    $this->_apiKeyHeader)));
        }
        else
        {
            $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                CURLOPT_VERBOSE => $verbose,
                CURLOPT_HTTPGET => TRUE,
                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                    $this->_authorization,
                    $this->_apiKeyHeader)));
        }
        $response = array();
        if(is_array($resources))
        {
            $mh = curl_multi_init();
            $handles = array();
            foreach($resources as $resource)
            {
                $url = $this->_rootUrl.'Presentations(\''.$resource.'\')ThumbnailContent';
                $this->AddOptions(array(CURLOPT_URL => $url));
                $handles[$url] = curl_init($url);
                curl_setopt_array($handles[$url], $this->GetOptions());
                curl_multi_add_handle($mh, $handles[$url]);
            }
            $running = null;

            do {
                curl_multi_exec($mh, $running);
                usleep(100000);
            } while ($running > 0);

            foreach ($handles as $key => $value)
            {
                if(curl_errno($value))
                {
                    $errormsg =  "GetThumbnailContentForPresentation ($key) error: " . curl_error($value);
                    $response[] = $errormsg;
                }
                else
                {
                    $header_size = curl_getinfo($value, CURLINFO_HEADER_SIZE);
                    $result = curl_multi_getcontent($value);
                    $header = substr($result, 0, $header_size);
                    $cookie = $this->FindCookie($header);
                    if($cookie !== FALSE)
                    {
                        $this->_cookie = $cookie;
                    }
                    $body = substr($result, $header_size);
                    $json = json_decode($body);
                    foreach($json->value as $content) {
                        $thumbnailContentRepresentation = new Sonicfoundry\ThumbnailContent($content);
                        $response[] = $thumbnailContentRepresentation;
                    }
                }

                curl_multi_remove_handle($mh, $value);
                curl_close($value);
            }
            return $response;
        }
        else
        {
            $url = $this->_rootUrl.'Presentations(\''.$resources.'\')ThumbnailContent';
            $this->AddOptions(array(CURLOPT_URL => $url));
            curl_setopt_array($this->_ch, $this->GetOptions());
            if( ! $result = curl_exec($this->_ch))
            {
                $errormsg =  'GetThumbnailContentForPresentation error: ' . curl_error($this->_ch);
                throw new Exception($errormsg);
            }
            else
            {
                $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                $header = substr($result, 0, $header_size);
                $cookie = $this->FindCookie($header);
                if($cookie !== FALSE)
                {
                    $this->_cookie = $cookie;
                }
                $body = substr($result, $header_size);
                $json = json_decode($body);
                foreach($json->value as $content) {
                    $thumbnailContentRepresentation = new Sonicfoundry\ThumbnailContent($content);
                    $response[] = $thumbnailContentRepresentation;
                }
                return $response;
            }
        }
    }
    function GetSlideContentForPresentation($resources, $verbose=FALSE) {
        if(empty($resources))
        {
            // Empty/null resource id
            throw new Exception('Empty/null presentation id');
        }
        if($this->_cookie != null && !empty($this->_cookie))
        {
            $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                CURLOPT_VERBOSE => $verbose,
                CURLOPT_HTTPGET => TRUE,
                CURLOPT_COOKIE => $this->_cookie,
                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                    $this->_apiKeyHeader)));
        }
        else
        {
            $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                CURLOPT_VERBOSE => $verbose,
                CURLOPT_HTTPGET => TRUE,
                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                    $this->_authorization,
                    $this->_apiKeyHeader)));
        }
        $response = array();
        if(is_array($resources))
        {
            $mh = curl_multi_init();
            $handles = array();
            foreach($resources as $resource)
            {
                $url = $this->_rootUrl.'Presentations(\''.$resource.'\')SlideContent';
                $this->AddOptions(array(CURLOPT_URL => $url));
                $handles[$url] = curl_init($url);
                curl_setopt_array($handles[$url], $this->GetOptions());
                curl_multi_add_handle($mh, $handles[$url]);
            }
            $running = null;

            do {
                curl_multi_exec($mh, $running);
                usleep(100000);
            } while ($running > 0);

            foreach ($handles as $key => $value)
            {
                if(curl_errno($value))
                {
                    $errormsg =  "GetSlideContentForPresentation ($key) error: " . curl_error($value);
                    $response[] = $errormsg;
                }
                else
                {
                    $header_size = curl_getinfo($value, CURLINFO_HEADER_SIZE);
                    $result = curl_multi_getcontent($value);
                    $header = substr($result, 0, $header_size);
                    $cookie = $this->FindCookie($header);
                    if($cookie !== FALSE)
                    {
                        $this->_cookie = $cookie;
                    }
                    $body = substr($result, $header_size);
                    $json = json_decode($body);
                    foreach($json->value as $content) {
                        $slideContentRepresentation = new Sonicfoundry\SlideContent($content);
                        $response[] = $slideContentRepresentation;
                    }
                }

                curl_multi_remove_handle($mh, $value);
                curl_close($value);
            }
            return $response;
        }
        else
        {
            $url = $this->_rootUrl.'Presentations(\''.$resources.'\')SlideContent';
            $this->AddOptions(array(CURLOPT_URL => $url));
            curl_setopt_array($this->_ch, $this->GetOptions());
            if( ! $result = curl_exec($this->_ch))
            {
                $errormsg =  'GetSlideContentForPresentation error: ' . curl_error($this->_ch);
                throw new Exception($errormsg);
            }
            else
            {
                $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                $header = substr($result, 0, $header_size);
                $cookie = $this->FindCookie($header);
                if($cookie !== FALSE)
                {
                    $this->_cookie = $cookie;
                }
                $body = substr($result, $header_size);
                $json = json_decode($body);
                foreach($json->value as $content) {
                    $slideContentRepresentation = new Sonicfoundry\SlideContent($content);
                    $response[] = $slideContentRepresentation;
                }
                return $response;
            }
        }
    }
    function QueryPresentationById($resources, $verbose=FALSE)
    {
        if(empty($resources))
        {
            // Empty/null resource id
            throw new Exception('Empty/null presentation id');
        }
        if($this->_cookie != null && !empty($this->_cookie))
        {
            $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                                    CURLOPT_VERBOSE => $verbose,
                                    CURLOPT_HTTPGET => TRUE,
                                    CURLOPT_COOKIE => $this->_cookie,
                                    CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                                 $this->_apiKeyHeader)));
        }
        else
        {
            $this->AddOptions(array(CURLOPT_HEADER => TRUE,
                                    CURLOPT_VERBOSE => $verbose,
                                    CURLOPT_HTTPGET => TRUE,
                                    CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                                 $this->_authorization,
                                                                 $this->_apiKeyHeader)));
        }
        if(is_array($resources))
        {
            $mh = curl_multi_init();
            $handles = array();
            $response = array();
            foreach($resources as $resource)
            {
                $url = $this->_rootUrl.'Presentations(\''.$resource.'\')?$select=full';
                $this->AddOptions(array(CURLOPT_URL => $url));
                $handles[$url] = curl_init($url);
                curl_setopt_array($handles[$url], $this->GetOptions());
                curl_multi_add_handle($mh, $handles[$url]);
            }
            $running = null;

            do {
                curl_multi_exec($mh, $running);
                usleep(100000);
            } while ($running > 0);

            foreach ($handles as $key => $value)
            {
                if(curl_errno($value))
                {
                    $errormsg =  "QueryPresentationById ($key) error: " . curl_error($value);
                    $response[] = $errormsg;
                }
                else
                {
                    $header_size = curl_getinfo($value, CURLINFO_HEADER_SIZE);
                    $result = curl_multi_getcontent($value);
                    $header = substr($result, 0, $header_size);
                    $cookie = $this->FindCookie($header);
                    if($cookie !== FALSE)
                    {
                        $this->_cookie = $cookie;
                    }
                    $body = substr($result, $header_size);
                    $json = json_decode($body);
                    $presentationRepresentation = new Sonicfoundry\Presentation($json);
                    $presentationRepresentation->Play = $json->{'#Play'}->target;
                    $response[] = $presentationRepresentation;
                }

                curl_multi_remove_handle($mh, $value);
                curl_close($value);
            }
            return $response;
        }
        else
        {
            $url = $this->_rootUrl.'Presentations(\''.$resources.'\')?$select=full';
            $this->AddOptions(array(CURLOPT_URL => $url));
            curl_setopt_array($this->_ch, $this->GetOptions());
            if( ! $result = curl_exec($this->_ch))
            {
                $errormsg =  'QueryPresentationById error: ' . curl_error($this->_ch);
                throw new Exception($errormsg);
            }
            else
            {
                $header_size = curl_getinfo($this->_ch, CURLINFO_HEADER_SIZE);
                $header = substr($result, 0, $header_size);
                $cookie = $this->FindCookie($header);
                if($cookie !== FALSE)
                {
                    $this->_cookie = $cookie;
                }
                $body = substr($result, $header_size);
                $json = json_decode($body);
                $presentationRepresentation = new Sonicfoundry\Presentation($json);
                $presentationRepresentation->Play = $json->{'#Play'}->target;
                return $presentationRepresentation;
            }
        }
    }
    function QueryPresentationPlaybackUrl($resourceId, $verbose=FALSE)
    {
        if(empty($resourceId))
        {
            // Empty/null resource id
            throw new Exception('Empty/null resource id for presentation playback');
        }
        $url = $this->_rootUrl.'Presentations(\''.$resourceId.'\')?$select=full';
        $this->AddOptions(array(CURLOPT_HEADER => 0,
                                CURLOPT_VERBOSE => $verbose,
                                CURLOPT_URL => $url,
                                CURLOPT_HTTPGET => TRUE,
                                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                             $this->_authorization,
                                                             $this->_apiKeyHeader)));
        curl_setopt_array($this->_ch, $this->GetOptions());
        if( ! $result = curl_exec($this->_ch))
        {
            $errormsg =  'QueryPresentationPlaybackUrl error: ' . curl_error($this->_ch);
            throw new Exception($errormsg);
        }
        else
        {
            $json = json_decode($result);
            return $json->{'#Play'}->target;
        }
    }
    function QueryCatalogById($resourceId, $verbose=FALSE)
    {
        if(empty($resourceId))
        {
            // Empty/null resource id
            throw new Exception('Empty/null catalog id');
        }
        $url = $this->_rootUrl.'Catalogs(\''.$resourceId.'\')';
        $this->AddOptions(array(CURLOPT_HEADER => 0,
                                CURLOPT_VERBOSE => $verbose,
                                CURLOPT_URL => $url,
                                CURLOPT_HTTPGET => TRUE,
                                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                             $this->_authorization,
                                                             $this->_apiKeyHeader)));
        curl_setopt_array($this->_ch, $this->GetOptions());
        if( ! $result = curl_exec($this->_ch))
        {
            $errormsg =  'QueryCatalogById error: ' . curl_error($this->_ch);
            throw new Exception($errormsg);
        }
        else
        {
            $json = json_decode($result);
            return new Sonicfoundry\Catalog($json);
        }
    }
    function CreateAuthTicket($username, $resourceId, $ip, $duration, $verbose=FALSE)
    {
        if(empty($resourceId))
        {
            // Empty/null resource id
            throw new Exception('Empty/null resource id for auth ticket');
        }
        $url = $this->_rootUrl.'AuthorizationTickets';
        $this->AddOptions(array(CURLOPT_HEADER => 0,
                                CURLOPT_VERBOSE => $verbose,
                                CURLOPT_URL => $url,
                                CURLOPT_POST => TRUE,
                                CURLOPT_POSTFIELDS => json_encode(array("Username"=>$username, "ClientIpAddress"=> $ip, "ResourceId"=> $resourceId, "MinutesToLive"=>$duration)),
                                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                             'Content-Type: '.MEDIASITE_WEBAPI_JSONCONTENTTYPE,
                                                             $this->_authorization,
                                                             $this->_apiKeyHeader)));
        curl_setopt_array($this->_ch, $this->GetOptions());
        if( ! $result = curl_exec($this->_ch))
        {
            $errormsg =  'CreateAuthTicket error: ' . curl_error($this->_ch);
            throw new Exception($errormsg);
        }
        else
        {
            $json = json_decode($result);
            return $json->TicketId;
        }
    }
    function GetApiKeyByName($apiname = "MoodlePlugin", $verbose=FALSE)
    {
        if(empty($apiname))
        {
            // Empty/null key name
            throw new Exception('Empty/null API Key Name');
        }
        if(!is_null($this->_apiKey) && $this->_apiKey->Name === $apiname)
        {
            return $this->_apiKey;
        }
        $url = $this->_rootUrl.'ApiKeys?$filter=Name%20eq%20\''.$apiname.'\'';
        $this->AddOptions(array(CURLOPT_HEADER => 0,
                                CURLOPT_VERBOSE => $verbose,
                                CURLOPT_URL => $url,
                                CURLOPT_HTTPGET => TRUE,
                                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                             $this->_authorization)));
        curl_setopt_array($this->_ch, $this->GetOptions());
        if( ! $result = curl_exec($this->_ch))
        {
            $errormsg =  'GetApiKeyByName error: ' . curl_error($this->_ch);
            throw new Exception($errormsg);
        }
        $json = json_decode($result);
        if(is_array($json->value) && count($json->value) > 0)
        {
            return new Sonicfoundry\ApiKey($json->value[0]);
        }
        return false;
    }
    function CreateApiKey($apiname = "MoodlePlugin", $verbose=FALSE)
    {
        if(empty($apiname))
        {
            // Empty/null key name
            throw new Exception('Empty/null API Key Name');
        }
        $url = $this->_rootUrl.'ApiKeys';
        $this->AddOptions(array(CURLOPT_HEADER => 0,
                                CURLOPT_VERBOSE => $verbose,
                                CURLOPT_URL => $url,
                                CURLOPT_POST => TRUE,
                                CURLOPT_POSTFIELDS => json_encode(array("Name"=>$apiname)),
                                CURLOPT_HTTPHEADER => array ('Accept: ' . MEDIASITE_WEBAPI_JSONACCEPT,
                                                             'Content-Type: '.MEDIASITE_WEBAPI_JSONCONTENTTYPE,
                                                             $this->_authorization)));
        curl_setopt_array($this->_ch, $this->GetOptions());
        if( ! $result = curl_exec($this->_ch))
        {
            $errormsg =  'CreateApiKey error: ' . curl_error($this->_ch);
            throw new Exception($errormsg);
        }
        $json = json_decode($result);
        if(is_array($json->value) && count($json->value) > 0)
        {
            return new Sonicfoundry\ApiKey($json->value[0]);
        }
        return false;
    }
}

?>