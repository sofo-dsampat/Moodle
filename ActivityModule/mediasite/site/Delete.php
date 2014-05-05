<?php
$siteid = required_param('site', PARAM_INT);          // site

global $DB;

// Delete the record
$DB->delete_records('mediasite_sites', array('id'=>$siteid));

// Go home
redirect("Configuration.php");