<?php
require_once("mod_mediasite_site_form.php");

//$siteid = required_param('site', PARAM_INT);
$siteid = optional_param('site', 0, PARAM_INT);

$context = context_system::instance();

global $CFG,$PAGE;

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/site/edit.php');

require_login();
require_capability('mod/mediasite:searchforcontent', $context);

global $DB;

$record = $DB->get_record('mediasite_sites', array('id'=>$siteid));

$site = new Sonicfoundry\MediasiteSite($record);
$editform = new mod_mediasite_site_form($site);
$mform =& $editform;
if ($mform->is_cancelled()) {
    // Go home
    redirect("Configuration.php");
}
$data = $mform->get_data();
if($data) {
    // Save edited data
    $site->set_sitename($data->sitename);
    $site->set_endpoint($data->siteurl);
    $site->set_username($data->siteusername);
    $site->set_password($data->sitepassword);
    $site->set_duration($data->siteduration);
    if(isset($data->siterestricttoip)) {
        $site->set_restrictip($data->siterestricttoip);
    } else {
        $site->set_restrictip(0);
    }
    if(isset($data->sitepassthru)) {
        $site->set_passthru($data->sitepassthru);
    } else {
        $site->set_passthru(0);
    }
    $site->update_database();
    // Go home
    redirect("Configuration.php");
}

global $OUTPUT;

echo $OUTPUT->header();

echo "<table border=\"0\" style=\"margin-left:auto;margin-right:auto\" cellspacing=\"3\" cellpadding=\"3\" width=\"640\">";
echo "<tr>";
echo "<td colspan=\"2\">";

$mform->display();

echo '</td></tr></table>';

echo $OUTPUT->footer();
