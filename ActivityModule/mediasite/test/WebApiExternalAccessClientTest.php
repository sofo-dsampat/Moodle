<?php

class WebApiExternalAccessClientTest extends PHPUnit_Framework_TestCase {
    private static $client;
    public static function setUpBeforeClass()
    {
        $GLOBALS['CFG'] = new stdClass();
        global $CFG;
        $CFG->dirroot = "C:/xampp/apps/moodle/htdocs/";
        require_once("$CFG->dirroot/mod/mediasite/webapiclient.php");

        // Force traffic through Fiddler proxy
        //$proxy = '127.0.0.1:8888';
        //WebApiExternalAccessClientTest::$client = new WebApiExternalAccessClient('http://kevinb-3500.sonicfoundry.net/Mediasite/7_0/api/v1/', 'MediasiteAdmin','New_Password', $proxy);

        WebApiExternalAccessClientTest::$client = new WebApiExternalAccessClient('http://kevinb-3500.sonicfoundry.net/Mediasite/7_0/api/v1/', 'MediasiteAdmin','New_Password');
    }
    public static function tearDownAfterClass()
    {
        WebApiExternalAccessClientTest::$client->Close();

        unset($GLOBALS['CFG']);
    }

    protected function setUp()
    {
        //fwrite(STDOUT, __METHOD__ . "\n");
    }
    protected function tearDown()
    {
        //fwrite(STDOUT, __METHOD__ . "\n");
    }

    protected function assertPreConditions()
    {
        //fwrite(STDOUT, __METHOD__ . "\n");
    }
    protected function assertPostConditions()
    {
        //fwrite(STDOUT, __METHOD__ . "\n");
    }

    protected function onNotSuccessfulTest(Exception $e)
    {
        fwrite(STDOUT, __METHOD__ . "\n");
        throw $e;
    }
    public function testGetSiteProperties()
    {
        $home = self::$client->QuerySiteProperties();
        $this->assertTrue(TRUE);
    }
    public function testGetPresentations()
    {
        $presentations = self::$client->QueryAllPresentations('?$top=100');
        $this->assertTrue(TRUE);
    }
    public function testGetOrderedPresentations()
    {
        $presentations = self::$client->QueryAllPresentations('?$orderby=Title&$top=100');
        $this->assertTrue(TRUE);
    }
    public function testGetPresentationsById()
    {
        $presentations = self::$client->QueryAllPresentations('?$top=100');
        foreach($presentations as $presentation)
        {
            $presentationById = self::$client->QueryPresentationById($presentation->Id);
        }
         $this->assertTrue(TRUE);
    }
    public function testGetPresentationByParallelId()
    {
        $presentations = self::$client->QueryAllPresentations('?$top=100');
        $ids = array();
        foreach($presentations as $presentation)
        {
            $ids[] = $presentation->Id;
            if(count($ids) > 199)
            {
                $parallelPresentations = self::$client->QueryPresentationById($ids);
                $ids = array();
            }
        }
        if(count($ids) > 0)
        {
            $parallelPresentations = self::$client->QueryPresentationById($ids);
            $ids = array();
        }
        $this->assertTrue(TRUE);
    }
    public function testGetPresentationPlaybackUrl()
    {
        $presentations = self::$client->QueryAllPresentations('?$top=100');
        foreach($presentations as $presentation)
        {
            $playbackUrl = self::$client->QueryPresentationPlaybackUrl($presentation->Id);
        }
        $this->assertTrue(TRUE);
    }
    public function testGetCatalogs()
    {
        $catalogs = self::$client->QueryCatalogShares();
        $this->assertTrue(TRUE);
    }
    public function testGetOrderedCatalogs()
    {
        $catalogs = self::$client->QueryCatalogShares('?$orderby=Name');
        $this->assertTrue(TRUE);
    }
    public function testGetApiByName()
    {
        $keys = self::$client->GetApiKeyByName();
        $this->assertTrue(TRUE);
    }
    public function testCreatePresentationAuthTickets()
    {
        $clientip = null;
        $duration = 5;
        $username = 'kevinb';
        $presentations = self::$client->QueryAllPresentations('?$top=100');
        foreach($presentations as $presentation)
        {
            $authTicketId = self::$client->CreateAuthTicket($username, $presentation->Id, $clientip, $duration);
        }
        $this->assertTrue(TRUE);
    }
}
 