<?php
    global $CFG;
    require_once('../../config.php');
    require_once("$CFG->dirroot/mod/mediasite/search_form.php");
    require_once("$CFG->dirroot/mod/mediasite/locallib.php");
    require_once("$CFG->dirroot/mod/mediasite/MediasiteSite.php");

    global $PAGE;
 
    $courseid = required_param('course', PARAM_INT);          // course
    $siteid = required_param('site', PARAM_INT);       // site
    $source = optional_param('searchsubmit', '', PARAM_TEXT); // source
    global $DB;
    $record = $DB->get_record('mediasite_sites', array('id'=>$siteid));

        $site = new Sonicfoundry\MediasiteSite($record);
        if(!$site) {
            ?>
            <script type="text/javascript">
                //<![CDATA[
                window.close();
                //]]>
            </script>
            <?php
            exit;
        }

        $site->set_config();

	//$context = get_context_instance(CONTEXT_COURSE, $courseid);
    $context = context_course::instance($courseid);

    require_login();
	require_capability('mod/mediasite:searchforcontent', $context);

    $PAGE->set_context($context);
	$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/search.php');
    html_header();

    //$mform =& new mod_mediasite_search_form(strval($courseid), compact('cm', 'mediasite', 'hook', 'mode', 'e', 'context'));
    $searchform = new mod_mediasite_search_form(strval($courseid), strval($siteid));
    $mform =& $searchform;

    if ($mform->is_cancelled()) {
        ?>
        <script type="text/javascript">
        //<![CDATA[
        window.close();
        //]]>
        </script>
        <?php
        exit;
    }

    if ($data = $mform->get_data()) {

        ?>
        <script type="text/javascript">
        //<![CDATA[
        function set_value(name, id, resourcetype) {
            opener.document.getElementById('id_name').value = name;
            opener.document.getElementById('id_resourceid').value = id;
            opener.document.getElementById('id_resourcetype').value = resourcetype;
            window.close();
        }
        //]]>
        </script>
        <?php

        $mform->display();
        $results = mediasite_search($data->searchtext,$data->resourcetype);
        $selectlabel = get_string('mediasitesearchchoose','mediasite');
        $table = new html_table();
        if(count($results) > 0) {
            foreach($results as $result) {
                $table->data[] = get_result_item($result, $data->resourcetype, $selectlabel);
            }
        }
        
        if(isset($table)) {
            echo html_writer::table($table);
        }
        else {
            echo get_string('mediasitesearchnoresult','mediasite');
        }
    }
    else
    {
        $mform->display();
    }

    html_footer();

    function get_result_item($result, $resourcetype, $selectlabel) {        
        $escapedname = str_replace("'","\'",$result->Name);
        $escapedname = str_replace('"', "'+String.fromCharCode(34)+'", $escapedname);
        $link = "<strong><a onclick=\"return set_value('$escapedname','$result->Id','$resourcetype')\" href=\"#\">$selectlabel</a></strong>";
        if($resourcetype == 'Presentation')
        {
            date_default_timezone_set('UTC');
            $recorddate = userdate(strtotime($result->RecordDate));
            return array ($link, "<strong>$result->Name</strong></br><em>$recorddate</em><br/>$result->Description");
            //return array ($link, format_string($result->Name));
        }
        elseif($resourcetype == 'Catalog')
        {
            return array ($link, format_string($result->Name));
        }
        else
        {
            throw new Exception("Invalid resource type $resourcetype");
        }
    }

    function html_header() {
        GLOBAL $OUTPUT;

        echo $OUTPUT->header();
        //print_header();

        echo "<table border=\"0\" style=\"margin-left:auto;margin-right:auto\" cellspacing=\"3\" cellpadding=\"3\" width=\"640\">";
        echo "<tr>";
        echo "<td colspan=\"2\">";
    }

    function html_footer() {
        global $COURSE, $OUTPUT;

        echo '</td></tr></table>';

        echo $OUTPUT->footer($COURSE);
    }

?>