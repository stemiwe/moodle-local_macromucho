<?php
// This file is part of local_checkmarkreport for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package   local_macromucho
 * @copyright 2019 Stefan Weber <webers@technikum-wien.at>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

class macromucho_form extends moodleform {
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;
        $courseid = $this->_customdata['courseid'];
        $context = $this->_customdata['context'];
        $qtypes = $this->_customdata['qtypes'];
        $qtypenames = $this->_customdata['qtypenames'];

        // Add form elements
        $mform->addElement('static', 'info', get_string('infolabel', 'local_macromucho'),
            get_string('infotext', 'local_macromucho'));

        $mform->addElement('static', 'howtouse', get_string('howtouselabel', 'local_macromucho'),
            get_string('howtouse', 'local_macromucho'));

        // Category selection
        $contexts = new question_edit_contexts($context);
        if (!empty($this->question->formoptions->mustbeusable)) {
            $contexts = $contexts->having_add_and_use();
        } else {
            $contexts = $contexts->having_cap('moodle/question:add');
        }

        $mform->addElement('questioncategory', 'category', get_string('questioncategory', 'question'), array('contexts' => $contexts));

        $mform->addElement('select', 'qtype', get_string('questiontype', 'admin'), $qtypenames);

        $mform->addElement('select', 'singleormulti', get_string('answerhowmany', 'qtype_multichoice'),
            [get_string('answersingleno', 'qtype_multichoice'), get_string('answersingleyes', 'qtype_multichoice')]);

        $mform->hideif('singleormulti', 'qtype', 'neq', array_search('multichoice', $qtypes));

        foreach ($qtypes as $qtypename) {
            $mform->addElement('textarea', $qtypename . '_importdata', get_string('importdata_description', 'local_macromucho') .
                get_string('importdata_copypastebutton', 'local_macromucho') . '<br><br>' .
                get_string('help_' . $qtypename, 'local_macromucho'), array('rows' => 12, 'cols' => 200));
            $mform->setDefault($qtypename . '_importdata', get_string('importdata_' . $qtypename, 'local_macromucho'));
            $mform->setType($qtypename . '_importdata', PARAM_RAW);
            $mform->hideif($qtypename . '_importdata', 'qtype', 'neq', array_search($qtypename, $qtypes));
        }

        $this->add_action_buttons($cancel = true,$submitlabel = get_string('submit'));

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
    }

    public function validation($data, $files) {
    $errors = parent::validation($data, $files);
    return $errors;
    }

}
