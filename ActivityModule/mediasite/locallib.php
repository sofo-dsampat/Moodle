<?php
require_once("$CFG->dirroot/mod/mediasite/webapiclient.php");
require_once("$CFG->dirroot/mod/mediasite/MediasiteSite.php");
require_once("$CFG->dirroot/mod/mediasite/MediasiteConfiguration.php");

function mediasite_get_version () {
    global $MEDIASITE;
    if($MEDIASITE->passthru === 1) {
        global $USER;
        $client = new WebApiExternalAccessClient($MEDIASITE->endpoint,$MEDIASITE->username,$MEDIASITE->password,$USER->username);
    } else {
        $client = new WebApiExternalAccessClient($MEDIASITE->endpoint,$MEDIASITE->username,$MEDIASITE->password);
    }

    $siteprops = $client->QuerySiteProperties();

    return $siteprops->Version;
}
function mediasite_check_resource_permission($resourceid, $resourcetype, $username)
{
    if ($resourcetype == 'Presentation') {
    } elseif ($resourcetype == 'Catalog') {
    }
    return true;
}

function mediasite_search($searchtext, $resourcetype)
{
    global $MEDIASITE;
    if($MEDIASITE->passthru == 1) {
        global $USER;
        $client = new WebApiExternalAccessClient($MEDIASITE->endpoint,$MEDIASITE->username,$MEDIASITE->password,$USER->username);
    } else {
        $client = new WebApiExternalAccessClient($MEDIASITE->endpoint,$MEDIASITE->username,$MEDIASITE->password);
    }

    if($resourcetype == 'Presentation') {
        if(strpos($searchtext, '*') === TRUE)
        {
            $filter = '?$filter=Title+eq+%27'.$searchtext.'%27+or+Description+eq+%27'.$searchtext.'%27+or+Tags%2Fany%28x%3Ax%2FTag+eq+%27'.$searchtext.'%27%29&$select=full';
        }
        elseif(empty($searchtext))
        {
            $results = $client->QueryPresentations('?$orderby=Title&$select=full');
        }
        else
        {
            $filter = '?$filter=%28'.
                          'Title+eq+%27'.$searchtext.'%27'.'+or+'.
                          'Description+eq+%27'.$searchtext.'%27'.'+or+'.
                          'Tags%2Fany%28x%3Ax%2FTag+eq+%27'.$searchtext.'%27%29'.
                      '%29'.
                      '&'.
                      '$orderby=Title+asc'.
                      '&'.
                      '$select=full';
            $results = $client->QueryPresentations($filter);
            if(count($results) <= 0) {
                $filter = '?$filter=%28'.
                              'startswith%28Title%2C+%27'.$searchtext.'%27%29'.'+or+'.
                              'startswith%28Description%2C+%27'.$searchtext.'%27%29'.'+or+'.
                              'Tags%2Fany%28x%3Astartswith%28x%2FTag%2C+%27'.$searchtext.'%27%29%29'.
                          '%29'.
                          '&'.
                          '$orderby=Title+asc'.
                          '&'.
                          '$select=full';
                $results = $client->QueryPresentations($filter);
            }
        }
    }
    else if($resourcetype == 'Catalog') {
        if(strpos($searchtext, '*') === TRUE)
        {
            $filter = '?$filter=%28Name+eq+%27'.$searchtext.'%27%29&$select=full';
        }
        elseif(empty($searchtext))
        {
            $results = $client->QueryCatalogShares('?$orderby=Name');
        }
        else
        {
            $filter = '?$filter=%28startswith%28Name%2C+%27'.$searchtext.'%27%29%29&$select=full';
            $results = $client->QueryCatalogShares($filter);
        }
    }

    if(count($results) == 1) {
        $results = array($results);
    }

    return $results;
}
function mediasite_get_playback_url($mediasitelink) {
    $site = new Sonicfoundry\MediasiteSite($mediasitelink->siteid);
    if(!$site) {
        error('Site not found - '.$mediasitelink->siteid);
        return '';
    } else {
        $site->set_config();
    }
    global $MEDIASITE;
    if($MEDIASITE->passthru == 1) {
        global $USER;
        $client = new WebApiExternalAccessClient($MEDIASITE->endpoint,$MEDIASITE->username,$MEDIASITE->password,$USER->username);
    } else {
        $client = new WebApiExternalAccessClient($MEDIASITE->endpoint,$MEDIASITE->username,$MEDIASITE->password);
    }

    if($mediasitelink->resourcetype == 'Presentation') {
        $playbackbase = $client->QueryPresentationPlaybackUrl($mediasitelink->resourceid);
    }
    else if($mediasitelink->resourcetype == 'Catalog') {
        $catalog = $client->QueryCatalogById($mediasitelink->resourceid);
        $playbackbase = $catalog->CatalogUrl;
    }

    if(!isset($playbackbase) || empty($playbackbase)) {
        print_error( get_string('mediasitenotfound', 'mediasite'));
        exit;
    }

    $clientip = null;
    if($MEDIASITE->restrictip == 1) {
        $clientip = $_SERVER['REMOTE_ADDR'];
    }
    global $USER;
    $authticket = $client->CreateAuthTicket($USER->username, $mediasitelink->resourceid, $clientip, $MEDIASITE->duration);

    $playbackurl = "$playbackbase?authTicket=$authticket";
    return $playbackurl;
}


?>
