<?php

namespace Sonicfoundry;

global $CFG;
require_once("$CFG->dirroot/lib/formslib.php");
require_once("$CFG->dirroot/mod/mediasite/lib.php");
require_once("$CFG->dirroot/mod/mediasite/MediasiteSite.php");

class mod_mediasite_siteselection_form extends \moodleform {
    private $siteList = null;
    function __construct($sites) {
        $this->siteList = $sites;
        parent::__construct();
    }
    function definition() {
        $mform    =& $this->_form;
        global $OUTPUT;
        if(is_array($this->siteList) && count($this->siteList) > 0) {
            $options = array();
            $table = new \html_table();
            $table->head = array("Site Name", "Endpoint", "User Name", "Duration", "Restrict IP", "Passthru", "Action");
            foreach($this->siteList as $site) {
                $options[$site->id] = $site->sitename;
                $cells = array();
                $cells[] = new \html_table_cell($site->sitename);
                $cells[] = new \html_table_cell($site->endpoint);
                $cells[] = new \html_table_cell($site->username);
                $cells[] = new \html_table_cell($site->duration);
                $cells[] = new \html_table_cell($site->restrictip);
                $cells[] = new \html_table_cell($site->passthru);
                $actioncell = new \html_table_cell();
                $actioncell->text = $OUTPUT->action_icon(new \moodle_url('/mod/mediasite/site/edit.php',
                            array('site' => $site->id)),
                        new \pix_icon('t/edit', 'Edit existing site'))
                    ." ".
                    $OUTPUT->action_icon(new \moodle_url('/mod/mediasite/site/delete.php',
                            array('site' => $site->id)),
                        new \pix_icon('t/delete', 'Delete existing site'));
                $cells[] = $actioncell;
                $row = new \html_table_row();
                $row->cells = $cells;
                $table->data[] = $row;
            }
            $mform->addElement('html', \html_writer::table($table));
        } else {
            $mform->addElement('html',  \html_writer::tag('p', \get_string('mediasitenosites', 'mediasite')));
        }

        $mform->addElement('html', $OUTPUT->action_icon(new \moodle_url('/mod/mediasite/site/add.php'),
            new \pix_icon('t/add', 'Add a site')));


        $mform->addElement('select', 'sites', \get_string('mediasitesitenames', 'mediasite'), $options);
        $this->add_action_buttons(TRUE, 'Save changes ?');
    }
} 