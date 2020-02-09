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

// Add to navigation block
function local_macromucho_extend_settings_navigation($navigation, $context) {
    global $CFG;
    if (has_capability('moodle/question:add', $context)) {
        // If not in a course context, then leave.
        if ($context == null || $context->contextlevel != CONTEXT_COURSE) {
            return;
        }

        // Front page has a 'frontpagesettings' node, other courses will have 'courseadmin' node.
        if (null == ($courseadminnode = $navigation->get('courseadmin'))) {
            // Keeps us off the front page.
            return;
        }

        if (null == ($navnode = $courseadminnode->get('questionbank', null))) {
            return;
        }

        // Add the nav entry
        $id = $context->instanceid;
        $urltext = get_string('pluginname', 'local_macromucho');
        $url = new moodle_url($CFG->wwwroot . '/local/macromucho/macromucho.php', array('courseid' => $id));
        $icon = new pix_icon('i/edit', '');
        $node = $navnode->create($urltext, $url, navigation_node::TYPE_SETTING, null, 'macromucho', $icon);
        $navnode->add_node($node, 'import');
    }
}

// Pick closest possible grade option for multiple choice questions
function pick_fraction($fraction) {
    if ((int)$fraction == 0) {return 0;}
    $fraction = round((int)$fraction / 100, 2);
    $gradeoptions = question_bank::fraction_options();
    $fractionoptions = array();
    foreach ($gradeoptions as $grade => $value) {
        array_push($fractionoptions, (float)$grade);
    }
    rsort($fractionoptions);
    foreach ($fractionoptions as $option) {
        if ($fraction + 0.01 >= $option) {return (string)$option;}
    }
    foreach ($fractionoptions as $option) {
        if ($fraction - 0.01 <= $option * -1) {return (string)$option * -1;}
    }
}

// Check if all required parameters for a given line are set
function check_required_params($nr, $questionvariables, $importlog) {
    for ($i = 1; $i < $nr; $i++) {
        if ($i > 3 && $i%2 == 0) {$i++;} // Jump over 'correct' columns
        if (!isset($questionvariables[$i]) || trim($questionvariables[$i]) == '') {
            array_push($importlog->text,
                get_string('question', 'moodle') . ' <b>'.
                $questionvariables[0] . '</b>: ' .
                '<span class="alert-danger">' .
                get_string('importerror', 'local_macromucho') . '</span>');
            $importlog->error++;
            $importlog->pass = false;
            return $importlog;
        }
    }
    $importlog->pass = true;
    return $importlog;
}

// Add successful import to output
function add_import_to_output($importlog, $question) {
    array_push($importlog->text,
        get_string('question', 'moodle') . ' <b>'.
        $question->name . '</b>: ' .
        '<span class="alert-success">' .
        get_string('importsuccess', 'local_macromucho') . '</span>');
    $importlog->success++;
    return $importlog;
}

// Set question options for MC questions TODO: maybe possible to replace with built-in function?
function set_mc_options($question) {
    $question->shuffleanswers = '1';
    $question->answernumbering = 'abc';
    //Set shownumcorrect to 0 for multichoiceset since this is the default value
    if ($question->qtype == 'multichoiceset') {
        $question->shownumcorrect = '0';
    } else {
        $question->shownumcorrect = '1';
    }
    $question->correctfeedback = array(
        'text' => get_string('correctfeedbackdefault', 'question'), 'format' => FORMAT_HTML);
    $question->partiallycorrectfeedback = array(
        'text' => get_string('partiallycorrectfeedbackdefault', 'question'), 'format' => FORMAT_HTML);
    $question->incorrectfeedback = array(
        'text' => get_string('incorrectfeedbackdefault', 'question'), 'format' => FORMAT_HTML);
    return $question;
}

// Fill in default question values.
function set_default_question_values($qtype, $catid, $context, $questionvariables) {
    global $USER, $CFG;

    $qtypeclassname = 'qtype_'.$qtype;
    require_once($CFG->dirroot . '/question/type/'.$qtype.'/questiontype.php');
    $question = new $qtypeclassname();
    $question->context = $context;
    $question->category = $catid;
    $question->parent = 0;
    $question->questiontextformat = FORMAT_HTML;
    $question->generalfeedback = '';
    $question->generalfeedbackformat = FORMAT_HTML;
    if ($qtype == 'truefalse') {
        $question->penalty = 0;
    } else {
        $question->penalty = 0.3333333;
    }
    $question->qtype = $qtype;
    $question->length = 1;
    $question->stamp = make_unique_id_code();
    $question->version = make_unique_id_code();
    $question->hidden = 0;
    $question->timecreated = time();
    $question->timemodified = time();
    $question->createdby = $USER->id;
    $question->modifiedby = $USER->id;
    // Fill in question properties from import form
    $question->name = $questionvariables[0];
    $question->questiontext = $questionvariables[1];
    $question->defaultmark = (float)$questionvariables[2];

    return $question;
}

/**
 * Process import form data
 *
 * @param string $qtype         name of question type to process
 * @param int $single           if it is a single choice question
 * @param int $catid            category id
 * @param object $context       Moodle context
 * @param string $importdata    data from the import form
 * @return object $importlog    result of import
 */
function macromucho_import($qtype, $single, $catid, $context, $importdata) {
    global $DB;
    // Clean input text
    $importdata = format_text($importdata, FORMAT_HTML);

    // Write each line of data into array and remove header
    $importquestions = preg_split('/\r\n|\r|\n/', $importdata);
    unset($importquestions[0]);

    // Initialize variables
    $questions = array();
    $importlog = new stdClass();
    $importlog->text = array();
    $importlog->success = 0;
    $importlog->error = 0;
    $importlog->pass = false;

    // Import questions based on question type
    // OUMULTIRESPONSE and MULTICHOICESET question types
    if ($qtype == 'oumultiresponse' || $qtype == 'multichoiceset' ) {
        foreach ($importquestions as $importquestion) {
            $questionvariables = explode("\t", $importquestion);

            if (!isset($questionvariables[0]) || trim($questionvariables[0]) == '') {return $importlog;}
            $importlog = check_required_params(6, $questionvariables, $importlog);

            if ($importlog->pass) {
                $question = set_default_question_values($qtype, $catid, $context, $questionvariables);
                $question = set_mc_options($question);

                // I don't know what this field does, but multichoiceset questions have it all set to 0
                if ($qtype == 'multichoiceset') {$question->layout = 0;}

                // Get answers
                $answercount = 0;
                for ($i = 3; ; $i = $i + 2) {
                    if (!array_key_exists($i, $questionvariables)) {break;}
                    $question->answer[$answercount] = array('text' => $questionvariables[$i], 'format' => FORMAT_HTML);
                    if (strcasecmp(trim($questionvariables[$i + 1]), 'x') == 0) {
                        $question->correctanswer[$answercount] = '1';
                    }
                    $question->feedback[$answercount] = array('text' => '', 'format' => FORMAT_HTML);
                    $answercount++;
                }

                // Save question and log success
                $question->id = $DB->insert_record('question', $question);
                $question->save_question_options($question);
                $importlog = add_import_to_output($importlog, $question);
            }
        }

    // MULTICHOICE question type
    } else if ($qtype == 'multichoice') {
        foreach ($importquestions as $importquestion) {
            $questionvariables = explode("\t", $importquestion);

            if (!isset($questionvariables[0]) || trim($questionvariables[0]) == '') {return $importlog;}
            $importlog = check_required_params(6, $questionvariables, $importlog);

            // Check answers for errors and evaluate fractions
            if ($importlog->pass) {
                $answercount = 0;
                $numcorrect = 0;
                $numincorrect = 0;
                $totalgrade = 0;
                $hasmax = false;

                for ($i = 3; ; $i = $i + 2) {
                    if (!array_key_exists($i, $questionvariables)) {break;}
                    if (!array_key_exists($i + 1, $questionvariables)) {$numincorrect++;}
                    else if (strcasecmp(trim($questionvariables[$i + 1]), 'x') == 0) {$numcorrect++;}
                    else if (trim($questionvariables[$i + 1] >= 0)) {
                        $totalgrade = $totalgrade + pick_fraction($questionvariables[$i + 1]);
                        if (trim($questionvariables[$i + 1] >= 100)) {$hasmax = true;}
                    }
                    else if (strcasecmp(trim($questionvariables[$i + 1]), '') == 0) {$numincorrect++;}
                    else if (strcasecmp(trim($questionvariables[$i + 1]), 'f') == 0) {$numincorrect++;}                   
                }
                // Check if at least one option gives 100% for single choice questions
                if ($single == 1 && $numcorrect < 1 && $hasmax == false) {
                    array_push($importlog->text,
                        get_string('question', 'moodle') . ' <b>'.
                        $questionvariables[0] . '</b>: ' .
                        '<span class="alert-danger">' .
                        get_string('errfractionsnomax', 'qtype_multichoice') . '</span>');
                    $importlog->error++;
                    $importlog->pass = false;
                }

                // Calculate automatically assigned fractions
                if ($numcorrect > 0) {$correctfraction = round(100 / $numcorrect, 7);}
                if ($numincorrect > 0) {$incorrectfraction = round(-100 / $numincorrect, 7);}

                // Set auto-assigned fractions for correct answers to 100 for single choice
                if ($single == 1) {$correctfraction = 100;}

                // Check for maximum number of correct answers
                if ($single == 0 && $numcorrect > 10) {
                    array_push($importlog->text,
                        get_string('question', 'moodle') . ' <b>'.
                        $questionvariables[0] . '</b>: ' .
                        '<span class="alert-danger">' .
                        get_string('toomanycorrect', 'local_macromucho') . '</span>');
                    $importlog->error++;
                    $importlog->pass = false;
                }

                // Check for maximum number of incorrect answers
                if ($single == 0 && $numincorrect > 10) {
                    array_push($importlog->text,
                        get_string('question', 'moodle') . ' <b>'.
                        $questionvariables[0] . '</b>: ' .
                        '<span class="alert-warning">' .
                        get_string('toomanyincorrect', 'local_macromucho') . '</span>');
                }

                // Check if the sum of all correct options is 100% for multiple choice questions
                if ($single == 0) {
                    $totalgrade = (int)round($totalgrade * 100 + $numcorrect * $correctfraction, 0);
                    if ($totalgrade !== 100) {
                        array_push($importlog->text,
                            get_string('question', 'moodle') . ' <b>'.
                            $questionvariables[0] . '</b>: ' .
                            '<span class="alert-danger">' .
                            get_string('errfractionsaddwrong', 'qtype_multichoice', $totalgrade) . '</span>');
                        $importlog->error++;
                        $importlog->pass = false;
                    }
                }
            }

            // Set question options
            if ($importlog->pass) {
                $question = set_default_question_values($qtype, $catid, $context, $questionvariables);
                $question = set_mc_options($question);
                $question->single = $single;

                // Get answers
                $answercount = 0;
                for ($i = 3; ; $i = $i + 2) {
                    if (!array_key_exists($i, $questionvariables)) {break;}
                    $question->answer[$answercount] = array('text' => $questionvariables[$i], 'format' => FORMAT_HTML);
                    if (!array_key_exists($i + 1, $questionvariables)) {
                        $question->fraction[$answercount] = 0;
                    } else if (strcasecmp(trim($questionvariables[$i + 1]), 'x') == 0) {
                        $question->fraction[$answercount] = pick_fraction($correctfraction);
                    } else if (strcasecmp(trim($questionvariables[$i + 1]), '') == 0) {
                        $question->fraction[$answercount] = pick_fraction($incorrectfraction);
                    } else if (strcasecmp(trim($questionvariables[$i + 1]), 'f') == 0) {
                        $question->fraction[$answercount] = pick_fraction($incorrectfraction);
                    } else {
                        $question->fraction[$answercount] = pick_fraction(trim($questionvariables[$i + 1]));
                        if (abs($question->fraction[$answercount] * 100 - $questionvariables[$i + 1]) > 1) {
                            array_push($importlog->text,
                                get_string('question', 'moodle') . ' <b>'.
                                $questionvariables[0] . '</b>: ' .
                                '<span class="alert-warning">' .
                                get_string('gradeadjusted', 'local_macromucho',
                                $questionvariables[$i + 1]) . $question->fraction[$answercount] * 100 . '%</span>');
                        }
                    }

                    $question->feedback[$answercount] = array('text' => '', 'format' => FORMAT_HTML);
                    $answercount++;
                }

                // Save question and log success
                $question->id = $DB->insert_record('question', $question);
                $question->save_question_options($question);
                $importlog = add_import_to_output($importlog, $question);
            }
        }


    } else if ($qtype == 'multichoiceset') {

    // TRUE/FALSE question type
    } else if ($qtype == 'truefalse') {
        foreach ($importquestions as $importquestion) {
            $questionvariables = explode("\t", $importquestion);

            if (!isset($questionvariables[0]) || trim($questionvariables[0]) == '') {return $importlog;}
            $importlog = check_required_params(4, $questionvariables, $importlog);

            if ($importlog->pass) {
                $question = set_default_question_values($qtype, $catid, $context, $questionvariables);

                // Set question options
                $question->feedbacktrue['format'] = FORMAT_HTML;
                $question->feedbackfalse['format'] = FORMAT_HTML;
                if (strcasecmp(trim($questionvariables[3]), 't') == 0) {
                    $question->correctanswer = 1;
                } else {
                    $question->correctanswer = 0;
                }

                // Save question and log success
                $question->id = $DB->insert_record('question', $question);
                $question->save_question_options($question);
                $importlog = add_import_to_output($importlog, $question);
            }
        }
    }
}
