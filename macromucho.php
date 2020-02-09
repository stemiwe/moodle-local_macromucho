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
 * Code for exporting questions as Moodle XML.
 *
 * @package    local_macromucho
 * @copyright  2019 onwards Stefan Weber <webers@technikum-wien.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Get params.
$courseid = required_param('courseid', PARAM_INT);
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourseid');
}

// Security and access check.
require_login($courseid, true);
$context = context_course::instance($courseid);
require_capability('moodle/question:add', $context);

// Create page.
$PAGE->set_pagelayout('incourse');
$PAGE->set_url(new moodle_url($CFG->wwwroot . '/local/macromucho/macromucho.php', array('courseid' => $courseid)));
$PAGE->set_title(get_string('pluginname', 'local_macromucho'));
$PAGE->set_heading(get_string('pluginname', 'local_macromucho'));

$renderer = $PAGE->get_renderer('local_macromucho');
echo $renderer->page_macromucho();

// Add javascript
$script = $CFG->wwwroot . '/local/macromucho/macromucho_script.js';
echo '<script src="' . $script . '"></script>';
exit;
