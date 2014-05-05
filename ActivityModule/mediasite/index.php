<?php

    require_once("../../config.php");
    require_once("$CFG->dirroot/mod/mediasite/lib.php");
	
    $inpopup  = optional_param('inpopup', 0, PARAM_BOOL);
    $id = required_param('id', PARAM_INT);   // course
	global $DB,$OUTPUT, $PAGE;
	
    if (! ($course = $DB->get_record("course", array ("id" => $id)))) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, "mediasite", "view all", "index.php?id=$course->id", "");
	$PAGE->set_url($CFG->wwwroot . '/mod/mediasite/view.php', array("id"=>$id, "inpopup"=>$inpopup));

    $strmediasites = get_string("modulenameplural", "mediasite");
    $strmediasite  = get_string("modulename", "mediasite");


    //$navlinks = array();
    //$navlinks[] = array('name' => $strmediasites, 'link' => '', 'type' => 'activity');
    //$navigation = build_navigation($navlinks);

    $PAGE->set_title("$strmediasites");
    $PAGE->set_heading("");
    $PAGE->set_cacheable(true);
    $PAGE->set_button("");
    echo $OUTPUT->header();
    // print_header_simple("$strmediasites", "", $navigation, "", "", true, "", navmenu($course));

    if (! $mediasites = get_all_instances_in_course("mediasite", $course)) {
        notice("There are no mediasites", "../../course/view.php?id=$course->id");
        die;
    }

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");
	
    $table = new html_table();
    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname);
        $table->align = array ("center", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ("center", "left", "left", "left");
    } else {
        $table->head  = array ($strname);
        $table->align = array ("left", "left", "left");
    }

    foreach ($mediasites as $mediasite) {
        if (!empty($mediasite->extra)) {
            $extra = urldecode($mediasite->extra);
        } else {
            $extra = "";
        }
        if (!$mediasite->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" $extra href=\"view.php?id=$mediasite->coursemodule\">".format_string($mediasite->name,true)."</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a foo='bar' $extra href=\"view.php?id=$mediasite->coursemodule\">".format_string($mediasite->name,true)."</a>";
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            #$table->data[] = array ($mediasite->section, $link);
            $cell1 = new html_table_cell($mediasite->section);
            $cell2 = new html_table_cell($link);
            $row = new html_table_row();
            $row->cells[] = $cell1;
            $row->cells[] = $cell2;
            $table->data[] = $row;
        } else {
	        $cell = new html_table_cell($link);
	        $row = new html_table_row();
	        $row->cells[] = $cell;
            $table->data[] = $row;
        }
    }

    echo "<br />";

    echo html_writer::table($table);
    
    echo $OUTPUT->footer($course);

?>
