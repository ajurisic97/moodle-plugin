<?php

require_once('../../config.php');
require_once("$CFG->libdir/formslib.php");

class sestireport_form extends moodleform {

    public function definition() {
        global $DB;
        global $CFG;
        $mform = & $this->_form;
        
		//Odabir studenta
		$options = array();
        $options[0] = 'Odaberi studenta:';
        $options += $this->_customdata['names'];
        $mform->addElement('select', 'name', "Student:", $options, 'align="center"');
        $mform->setType('name', PARAM_ALPHANUMEXT);
		
		//Odabir kolegija
		$options1 = array();
        $options1[0] = 'Odaberi kolegij:';
        $options1 += $this->_customdata['courses']; 
        $mform->addElement('select', 'course', "Kolegij:", $options1, 'align="center"');
        $mform->setType('course', PARAM_ALPHANUMEXT);
        
		
		//Odabir datuma
		/*$mform->addElement('date_selector', 'lastaccesseddate', get_string('from'), 'align="center"');
       $mform->setType('lastaccesseddate', PARAM_INT);
		$mform->addElement('date_selector', 'currentdate', get_string('to'), 'align="center"');
		$mform->setType('currentdate', PARAM_INT);*/
		
		//Odabir testa
		/*
		$options2 = array();
        $options2[0] = 'Odaberi test:';
        $options2 += $this->_customdata['tests'];
        $mform->addElement('select', 'test', "Test:", $options2, 'align="center"');
        $mform->setType('test', PARAM_ALPHANUMEXT);
		
		//Odabir lekcije
		$options3 = array();
        $options3[0] = 'Odaberi lekciju:';
        $options3 += $this->_customdata['lessons'];
        $mform->addElement('select', 'lesson', "Lekcija:", $options3, 'align="center"');
        $mform->setType('lesson', PARAM_ALPHANUMEXT);
		*/
        
		// Button za slanje zahtjeva
        $mform->addElement('submit', 'save', 'Prikaži', get_string('report_sestireport'), 'align="right"');
    }
}
?>