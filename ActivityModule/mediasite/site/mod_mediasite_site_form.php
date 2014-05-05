<?php
require_once(dirname(__FILE__) . '/../../../config.php');
require_once("$CFG->dirroot/lib/formslib.php");
require_once("$CFG->dirroot/mod/mediasite/lib.php");
require_once("$CFG->dirroot/mod/mediasite/MediasiteSite.php");

class mod_mediasite_site_form extends \moodleform {
    private $siteToEdit = null;
    function __construct(Sonicfoundry\MediasiteSite $site = null) {
        $this->siteToEdit = $site;
        parent::__construct();
    }
    function definition() {
        $mform    =& $this->_form;
//-------------------------------------------------------------------------------
        $mform->addElement('header', 'siteheader', get_string('mediasiteserverurl', 'mediasite'));
//-------------------------------------------------------------------------------
        if(is_null($this->siteToEdit)) {
            $mform->addElement('text', 'sitename', get_string('mediasitesitename', 'mediasite'));
            $mform->setType('sitename', PARAM_TEXT);
            $mform->addElement('text', 'siteurl', get_string('mediasiteserverurl', 'mediasite'));
            $mform->setType('siteurl', PARAM_TEXT);
            $mform->addElement('text', 'siteusername', get_string('mediasiteusername', 'mediasite'));
            $mform->setType('siteusername', PARAM_TEXT);
            $mform->addElement('passwordunmask', 'sitepassword', get_string('mediasitepassword', 'mediasite'));
            $mform->setType('sitepassword', PARAM_TEXT);
            $mform->addElement('text', 'siteduration', get_string('mediasiteticketduration', 'mediasite'));
            $mform->setType('siteduration', PARAM_INT);
            $mform->addElement('checkbox', 'siterestricttoip', get_string('mediasiterestricttoip', 'mediasite'));
            $mform->setType('siterestricttoip', PARAM_INT);
            $mform->addElement('checkbox', 'sitepassthru', get_string('mediasitepassthru', 'mediasite'));
            $mform->setType('sitepassthru', PARAM_INT);
            $mform->addElement('hidden', 'site', 0);
            $mform->setType('site', PARAM_INT);
            $this->add_action_buttons(TRUE, 'Add Site ?');
        } else {
            $mform->addElement('text', 'sitename', get_string('mediasitesitename', 'mediasite'));
            $mform->setType('sitename', PARAM_TEXT);
            $mform->setDefault('sitename',$this->siteToEdit->get_sitename());
            $mform->addElement('text', 'siteurl', get_string('mediasiteserverurl', 'mediasite'));
            $mform->setType('siteurl', PARAM_TEXT);
            $mform->setDefault('siteurl',$this->siteToEdit->get_endpoint());
            $mform->addElement('text', 'siteusername', get_string('mediasiteusername', 'mediasite'));
            $mform->setType('siteusername', PARAM_TEXT);
            $mform->setDefault('siteusername',$this->siteToEdit->get_username());
            $mform->addElement('passwordunmask', 'sitepassword', get_string('mediasitepassword', 'mediasite'));
            $mform->setType('sitepassword', PARAM_TEXT);
            $mform->setDefault('sitepassword',$this->siteToEdit->get_password());
            $mform->addElement('text', 'siteduration', get_string('mediasiteticketduration', 'mediasite'));
            $mform->setType('siteduration', PARAM_INT);
            $mform->setDefault('siteduration',$this->siteToEdit->get_duration());
            $mform->addElement('checkbox', 'siterestricttoip', get_string('mediasiterestricttoip', 'mediasite'));
            $mform->setType('siterestricttoip', PARAM_INT);
            $mform->setDefault('siterestricttoip',$this->siteToEdit->get_restrictip());
            $mform->addElement('checkbox', 'sitepassthru', get_string('mediasitepassthru', 'mediasite'));
            $mform->setType('sitepassthru', PARAM_INT);
            $mform->setDefault('sitepassthru',$this->siteToEdit->get_passthru());
            $mform->addElement('hidden', 'site', $this->siteToEdit->get_siteid());
            $mform->setType('site', PARAM_INT);
            $this->add_action_buttons(TRUE, 'Save changes ?');
        }
    }
}