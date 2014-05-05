<?php
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/mod/mediasite/lib.php');
require_once("mod_mediasite_siteselection_form.php");


// Check the user is logged in.
require_login();
$context = context_system::instance();

admin_externalpage_setup('activitysettingmediasite');
require_capability('mod/mediasite:searchforcontent', $context);

global $DB;
// Get the list of configured engines.
$sites = $DB->get_records('mediasite_sites');
$siteselectionform = new Sonicfoundry\mod_mediasite_siteselection_form($sites);
$mform =& $siteselectionform;
if ($mform->is_cancelled()) {
    // Go home
    redirect($CFG->wwwroot);
}
$data = $mform->get_data();
if($data) {
    $record = $DB->get_record('mediasite_sites', array('id'=>$data->sites));
    $site = new Sonicfoundry\MediasiteSite($record);
    $site->set_config();
    // Go home
    redirect($CFG->wwwroot);
}

global $OUTPUT;

echo $OUTPUT->header();

echo "<table border=\"0\" style=\"margin-left:auto;margin-right:auto\" cellspacing=\"3\" cellpadding=\"3\" width=\"640\">";
echo "<tr>";
echo "<td colspan=\"2\">";

$mform->display();

echo '</td></tr></table>';

echo $OUTPUT->footer();
