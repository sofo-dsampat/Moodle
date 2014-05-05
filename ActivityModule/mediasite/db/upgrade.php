<?php
require_once("$CFG->dirroot/mod/mediasite/webapiclient.php");

function xmldb_mediasite_upgrade($oldversion=0) {
	
	global $DB;
    $dbman = $DB->get_manager();
	
	$result = true;

    // Upgrade
    if($result && $oldversion == 2012032900)
    {
        // Define table mediasite_sites to be created.
        $table = new xmldb_table('mediasite_sites');

        // Adding fields to table mediasite_sites.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('sitename', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'Default');
        $table->add_field('endpoint', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('apikey', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, '');
        $table->add_field('username', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, 'MediasiteAdmin');
        $table->add_field('password', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '');
        $table->add_field('duration', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('restrictip', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('passthru', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');

        // Adding keys to table mediasite_sites.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('sitename', XMLDB_KEY_UNIQUE, array('sitename'));

        // Conditionally launch create table for mediasite_sites.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
		
        // Define field siteid to be added to mediasite.
        $table = new xmldb_table('mediasite');
        $field = new xmldb_field('siteid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        // Conditionally launch add field siteid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $key = new xmldb_key('siteidforeignkey', XMLDB_KEY_FOREIGN, array('siteid'), 'mediasite_sites', array('id'));
        // Launch add key siteidforeignkey.
        $dbman->add_key($table, $key);
		
		// At this point we have the new table and have updated the
		//old table with the new field.
		$site_record = array();
		$site_record['sitename'] = 'Default';
		$site_record['passthru'] = '0';
		$whereclause = 'name LIKE \'mediasite%\'';

		$config_records = $DB->get_records_sql("SELECT * FROM {config} WHERE $whereclause");
		foreach($config_records as $config_record) {
			if($config_record->name == 'mediasite_username') {
				$site_record['username'] = $config_record->value;
			} elseif($config_record->name == 'mediasite_password') {
				$site_record['password'] = $config_record->value;
			} elseif($config_record->name == 'mediasite_serverurl') {
                $site_record['endpoint'] = preg_replace('/6_1_7\/?$/', 'main', $config_record->value);
			} elseif($config_record->name == 'mediasite_ticketduration') {
				$site_record['duration'] = $config_record->value;
			} elseif($config_record->name == 'mediasite_restricttoip') {
				$site_record['restrictip'] = $config_record->value;
			}
		}

        if(!array_key_exists("endpoint", $site_record) ||
           !array_key_exists("username", $site_record) ||
           !array_key_exists("password", $site_record)) {
            return false;
        }
        $client = new WebApiExternalAccessClient($site_record['endpoint'],
                                                 $site_record['username'],
                                                 $site_record['password']);

        $siteproperties = $client->QuerySiteProperties();
        if(!preg_match('/7\.\d+\.\d+/', $siteproperties->SiteVersion)) {
            return false;
        }
        // Try to get the apiKey
        try {
            if(!($apiKey = $client->GetApiKeyByName())) {
                if(!($apiKey = $client->CreateApiKey())) {
                    return false;
                }
            }
            $site_record['apikey'] = $apiKey->Id;
        } catch(Exception $e) {
            if(!($apiKey = $client->CreateApiKey())) {
                return false;
            }
            $site_record['apikey'] = $apiKey->Id;
        }

        // Now we are modifying the database records

        $DB->delete_records_select('config', $whereclause);
        $site_id = $DB->insert_record('mediasite_sites', $site_record, true);

        $mediasite_rs = $DB->get_recordset('mediasite');
        if($mediasite_rs->valid()){
            foreach ($mediasite_rs as $mediasite_record) {
                $record = new stdClass();
                $record->id = $mediasite_record->id;
                $record->siteid = $site_id;
                $DB->update_record('mediasite', $record, true);
                //Be aware that from Moodle 2.6 onwards modinfo + sectioncache have been
                //removed from the mdl_course table - they are now stored in the Moodle cache.
                //This means that the only safe way to clear them is via
                rebuild_course_cache($mediasite_record->course, true);
            }
        }
        //$DB->execute('UPDATE {course} set modinfo = ?, sectiocache = ?', array(null, null));
        $mediasite_rs->close();
		
        upgrade_mod_savepoint(true, 2013121800, 'mediasite');
    }
	return $result;
}

?>
