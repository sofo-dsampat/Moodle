<?php

require_once ("$CFG->dirroot/course/moodleform_mod.php");
require_once("$CFG->dirroot/mod/mediasite/locallib.php");


class mod_mediasite_mod_form extends moodleform_mod {

function definition() {
    global $CFG;
    global $COURSE;
    global $DB;

    $mform =& $this->_form;


//-------------------------------------------------------------------------------
    
    $mform->addElement('header', 'general', get_string('general', 'form'));

    $searchbutton = $mform->addElement('button', 'searchbutton', get_string('mediasitesearchbutton','mediasite'));

//    $buttonattributes = array('title'=>get_string('mediasitesearchbutton', 'mediasite'), 'onclick'=>"return window.open('"
//        . "$CFG->wwwroot/mod/mediasite/search.php?course=". strval($COURSE->id)
//        . "&site='+sitename_value()+'"
//        . "', 'mediasitesearch', 'menubar=1,location=1,directories=1,toolbar=1,"
//        . "scrollbars,resizable,width=800,height=600');");
//    $selectedsitename = "sitename_value()";
    $buttonattributes = array('title'=>get_string('mediasitesearchbutton', 'mediasite'), 'onclick'=>"return window.open('"
        . "$CFG->wwwroot/mod/mediasite/search.php?course=". strval($COURSE->id)
        . "&site='+document.getElementById('id_siteid').value+'"
        . "', 'mediasitesearch', 'menubar=1,location=1,directories=1,toolbar=1,"
        . "scrollbars,resizable,width=800,height=600');");
    $searchbutton->updateAttributes($buttonattributes);

    $records = $DB->get_records('mediasite_sites');
    $sitenames = array();
    foreach($records as $record) {
        $sitenames[$record->id] = $record->sitename;
    }

    if(count($sitenames) > 0) {
        $selectdropdown = $mform->addElement('select','siteid', get_string('mediasitesitename','mediasite'), $sitenames);
        $selectdropdown->setSelected(reset($sitenames));
    }

    $resourcetypes = array('Presentation'=>get_string('mediasitepresentation','mediasite'),'Catalog'=>get_string('mediasitecatalog','mediasite'));
    $mform->addElement('select','resourcetype',get_string('mediasiteresourcetype','mediasite'),$resourcetypes,array('size'=>'1'));

    $mform->addElement('text', 'name', get_string('mediasitename', 'mediasite'), array('size'=>'64'));
    $mform->setType('name', PARAM_TEXT);
    $mform->addRule('name', null, 'required', null, 'server');
    
    $mform->addElement('text','resourceid', get_string('mediasiteresourceid','mediasite'),array('size'=>'64'));
    $mform->setType('resourceid', PARAM_TEXT);
    $mform->addRule('resourceid', null, 'required', null, 'server');
    
    $mform->addElement('selectyesno', 'openaspopup', get_string('mediasiteopenaspopup', 'mediasite'));
    $mform->setDefault('openaspopup', 1);
    

//-------------------------------------------------------------------------------
    // add standard elements, common to all modules
    $this->standard_coursemodule_elements();

//-------------------------------------------------------------------------------
    // add standard buttons, common to all modules
    $this->add_action_buttons();

}

function validation($data, $files) {

    $errors = parent::validation($data, $files);
    global $USER;

    try {
        //validate the current user has access to the selected resource
        $valid = mediasite_check_resource_permission($data['resourceid'], $data['resourcetype'], $USER->username);
        if(!$valid){
            $errors['resourceid'] = get_string('mediasitenotauthorized','mediasite');		
        }
    }
    catch(Exception $e) {
        $errors['resourceid'] = $e->getMessage();
    }

    return $errors;
}

}

?>
