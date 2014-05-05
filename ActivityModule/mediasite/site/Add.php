<?php
require_once("mod_mediasite_site_form.php");

$context = context_system::instance();

require_login();
require_capability('mod/mediasite:searchforcontent', $context);

global $PAGE;

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/site/add.php');
$addform = new mod_mediasite_site_form();
$mform =& $addform;

if ($mform->is_cancelled()) {
    redirect("Configuration.php");
}

global $OUTPUT;

echo $OUTPUT->header();

echo "<table border=\"0\" style=\"margin-left:auto;margin-right:auto\" cellspacing=\"3\" cellpadding=\"3\" width=\"640\">";
echo "<tr>";
echo "<td colspan=\"2\">";

$data = $mform->get_data();
if($data) {
    $client = new WebApiExternalAccessClient($data->siteurl, $data->siteusername, $data->sitepassword);
    $record = new stdClass();
    $apikey = null;
    try {
        $apikey = $client->GetApiKeyByName();
        $record->apikey = $apikey->Id;
    } catch(Exception $e) {
        try {
            $apikey = $client->CreateApiKey();
            $record->apikey = $apikey->Id;
        } catch(Exception $e) {
            // Go home on error
            redirect("Configuration.php");
        }
    }
    $record->sitename = $data->sitename;
    $record->endpoint = $data->siteurl;
    $record->username = $data->siteusername;
    $record->password = $data->sitepassword;
    $record->duration = $data->siteduration;
    $record->restrictip = $data->siterestricttoip;
    $record->passthru = $data->sitepassthru;

    global $DB;
    // Add new record
    $DB->insert_record('mediasite_sites', $record);
    // Go home
    redirect("Configuration.php");
}
$mform->display();

echo '</td></tr></table>';

echo $OUTPUT->footer();
