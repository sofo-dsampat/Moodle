<?php
//require_once('../../config.php');
require_once("$CFG->dirroot/lib/formslib.php");

function mediasite_add_instance($mediasite) {
    global $DB;
    $mediasite->timecreated = time();
    return $DB->insert_record("mediasite", $mediasite);
}
function mediasite_update_instance($mediasite) {
    global $DB;
    $mediasite->id = $mediasite->instance;
    $mediasite->timemodified = time();

    return $DB->update_record("mediasite", $mediasite);
}
function mediasite_delete_instance($mediasiteId) {
    global $DB;
    return $DB->delete_records("mediasite", array('id'=>$mediasiteId));
}
/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 * See get_array_of_activities() in course/lib.php
 *
 * @global object
 * @param object $coursemodule
 * @return cached_cm_info|null
 */
//function mediasite_get_coursemodule_info($coursemodule) {
//    global $DB;
//
//    if ($mediasite = $DB->get_record('mediasite', array('id'=>$coursemodule->instance), 'id, course, name, resourceid, resourcetype')) {
//        if (empty($mediasite->name)) {
//            // mediasite name missing, fix it
//            $mediasite->name = "label{$mediasite->id}";
//            $DB->set_field('mediasite', 'name', $mediasite->name, array('id'=>$mediasite->id));
//        }
//        $info = new cached_cm_info();
//        // no filtering hre because this info is cached and filtered later
//        $info->content = format_module_intro('mediasite', $mediasite, $coursemodule->id, false);
//        $info->name  = $mediasite->name;
//        return $info;
//    } else {
//        return null;
//    }
//}
function mediasite_user_complete($mediasite) {

}
function mediasite_user_outline($mediasite) {

}
function mediasite_cron($mediasite) {

}
function mediasite_print_recent_activity($mediasite) {
}

?>
