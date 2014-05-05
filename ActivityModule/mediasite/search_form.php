<?php
global $CFG;

require_once('../../config.php');
require_once("$CFG->dirroot/lib/formslib.php");
require_once("$CFG->dirroot/mod/mediasite/locallib.php");

class mod_mediasite_search_form extends moodleform {
    private $sid;
	function __construct($cid, $sid) {
		$this->cid = $cid;
        $this->sid = $sid;
		parent::__construct();
	}
	
    function definition() {
        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'searchheader', get_string('mediasitesearchbutton', 'mediasite'));

        $resourcetypes = array('Presentation'=>get_string('mediasitepresentation','mediasite'),'Catalog'=>get_string('mediasitecatalog','mediasite'));
        $mform->addElement('select','resourcetype',get_string('mediasiteresourcetype','mediasite'),$resourcetypes,array('size'=>'1'));
        $mform->setType('resourcetype', PARAM_TEXT);

        $mform->addElement('text', 'searchtext', get_string('mediasitesearchtext', 'mediasite'));
        $mform->setType('searchtext', PARAM_TEXT);

        $mform->addElement('hidden', 'course', $this->cid);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'site', $this->sid);
        $mform->setType('site', PARAM_INT);
        $mform->addElement('submit', 'searchsubmit', get_string('mediasitesearchsubmit', 'mediasite'));
        $mform->setType('searchsubmit', PARAM_TEXT);
//-------------------------------------------------------------------------------

    }

}
?>