<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/** 
 *
 * @package     local_macromucho
 * @copyright   2019 Stefan Weber <webers@technikum-wien.at>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_macromucho_renderer extends \plugin_renderer_base {

    /**
     * return content for mass enrolment page.
     */
    public function page_macromucho() {
        global $CFG, $USER, $OUTPUT;
        require_once($CFG->dirroot . '/local/macromucho/macromucho_form.php');
        require_once($CFG->dirroot . '/local/macromucho/lib.php');
        require_once($CFG->libdir . '/questionlib.php');
        require_once($CFG->dirroot . '/question/engine/bank.php');
        require_once($CFG->dirroot . '/question/category_class.php');

        // Get variables
        $course = $this->page->course;
        $context = $this->page->context;

        // Get all installed question types that are supported
        $supportedqtypes = ['oumultiresponse', 'multichoice', 'truefalse', 'multichoiceset'];
        foreach (question_bank::get_creatable_qtypes() as $qtype => $qtype) {
            if (in_array($qtype, $supportedqtypes)) {
                $qtypes[] = $qtype;
                $qtypenames[] = get_string('pluginname', 'qtype_'.$qtype);
            }
        }

        // Create form
        $mform = new macromucho_form(new moodle_url($CFG->wwwroot . '/local/macromucho/macromucho.php'), array(
            'courseid' => $course->id,
            'context' => $context,
            'qtypes' => $qtypes,
            'qtypenames' =>  $qtypenames));

        // Display header
        $out = '';
        $out .= $this->header($course);
        $out .= $OUTPUT->heading(get_string('pluginname', 'local_macromucho'));

        // If the form is cancelled, return to question bank
        if ($mform->is_cancelled()) {
            redirect(new moodle_url('/question/edit.php', array('courseid' => $course->id)));

        // Process submitted data
        } else if ($data = $mform->get_data()) {
            $qtype = $qtypes[$data->qtype];
            $importqtype = $qtype . '_importdata';
            $importdata = $data->{$importqtype};
            $single = $data->singleormulti;
            $category = explode(",", $data->category);
            $catid = $category[0];

            // Process import and get log
            $importlog = macromucho_import($qtype, $single, $catid, $context, $importdata);

            // Write results
            $categorylist = new question_category_list('ul', '', true, $this->pageurl, null, 'cpage', QUESTION_PAGE_LENGTH, $context);
            $categorylist->get_records();
            $out .= get_string('category', 'moodle'). ': <b>' . $categorylist->records[$catid]->name . '</b><br>';
            $out .= get_string('questiontype', 'question'). ': <b>';
            $out .= get_string('pluginname', 'qtype_'.$qtype) . '</b><br>';
            if ($qtype = 'multichoice') {
                $out .= get_string('answerhowmany', 'qtype_multichoice'). ': <b>';
                if ($single == 0) {
                    $out .= get_string('answersingleno', 'qtype_multichoice');
                } else if ($single == 1) {
                    $out .= get_string('answersingleyes', 'qtype_multichoice');
                }
            }
            $out .= '</b><br><br><ul>';
            foreach ($importlog->text as $importlogline) {
                $out .= "<li>" . $importlogline . "</li>";
            }
            $out .= '</ul>------------------------------------------------------------------<br>';
            $out .= get_string('importtotalsuccess', 'local_macromucho') . '<b>' . $importlog->success . '</b><br>';
            $out .= get_string('importtotalerror', 'local_macromucho') . '<b>' . $importlog->error . '</b>';
            $url = new moodle_url('/question/edit.php', array('courseid' => $course->id));
            $out .= '<br><br><a class="btn btn-primary" href="' . $url . '">' . get_string('questionbank', 'question') . '</a>';
            $out .= $this->footer($course);

            return $out;
        }

        // Show form initially
        $out .= $mform->render();
        $out .= $this->footer($course);

        return $out;
    }

}
