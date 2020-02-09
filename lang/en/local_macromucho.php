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
 * Strings for component 'qtype_macromucho', language 'en', branch 'MOODLE_37_STABLE'
 *
 * @package    local_macromucho
 * @copyright  2019 onwards Stefan Weber <webers@technikum-wien.at>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['gradeadjusted'] = 'Grade {$a}% was adjusted to nearest possible option ';
$string['help_multichoice'] = '<ul><li>Add two columns for every answer option - one contains the answer text and one the mark.
</li><li>You can either fill in the grades for the answers manually from 100 to -100, or use "x" for correct answers and "f" for incorrect ones to have them automatically add up to 100 or -100 respectively.
</li><li>When using Single choice, at least one answer must have a grade of 100, when using Multiple Choice, all the correct answers must add up to 100.</li></ul>';
$string['help_multichoiceset'] = '<ul><li>Add two columns for every possible answer - one contains the answer text and one the mark.
</li><li>Use "x" to mark correct answers.
</li><li>You can add up to 50 answer options.</li></ul>';
$string['help_oumultiresponse'] = '<ul><li>Add two columns for every possible answer - one contains the answer text and one the mark.
</li><li>Use "x" to mark correct answers.
</li><li>You can add up to 50 answer options.</li></ul>';
$string['help_truefalse'] = '<ul><li>In the 4th column, enter "t" for a true statement, "f" for a false one.</li></ul>';
$string['importdata_multichoice'] = 'Question name	Question text	Default mark	Answer 1	Grade	Answer 2	Grade	Answer 3	Grade	Answer 4	Grade	Answer 5	Grade	Answer 6	Grade	Answer 7	Grade
Multi choice example 1	Example for auto-calculated grades	1	Correct answer text	x	Correct answer text	X	Correct answer text	x	Correct answer text	x	Wrong answer text	f	Wrong answer text	f	Wrong answer text	f
Multi choice example 2	Example for manually set grades	1	Correct answer text	50	Correct answer text	50	Neutral answer text	0	Very wrong answer text	-100	Wrong answer text	-50	Wrong answer text	-50
Multi choice example 3	Example mixing both	1	Correct answer text	x	Correct answer text	x	Neutral answer text	0	Wrong answer text	-100
Single choice example 1	Example for auto-calculated grades	1	Correct answer text	X	Wrong answer text	f	Wrong answer text	f	Wrong answer text	f	Wrong answer text	f	Wrong answer text	f	Wrong answer text	f
Single choice example 2	Example for manually set grades	1	Correct answer text	100	Correct answer text	100	Correct answer text	100	Wrong answer text	f	Wrong answer text	f	Wrong answer text	f	Wrong answer text	f
Single choice example 3	Negative grades can be manually set	1	Correct answer text	X	Wrong answer text	-50	Wrong answer text	-25	Wrong answer text	-10	Wrong answer text	-100
';
$string['importdata_multichoiceset'] = 'Question name	Question text	Default Correct?	Answer 1	Correct?	Answer 2	Correct?	Answer 3	Correct?	Answer 4	Correct?	Answer 5	Correct?	Answer 6	Correct?	Answer 7	Correct?	Answer 8	Correct?	Answer 9	Correct?
All-or-nothing 1	This question has 2 correct answers	1	Correct answer text	x	Wrong answer text		Wrong answer text		Wrong answer text		Correct answer text	x
All-or-nothing 2	This question has 4 correct answers	1	Wrong answer text		Correct answer text	x	Wrong answer text		Correct answer text	x	Wrong answer text		Wrong answer text		Correct answer text	x	Correct answer text	x	Wrong answer text
All-or-nothing 3	This question has 7 correct answers	1	Correct answer text	x	Correct answer text	x	Correct answer text	x	Correct answer text	x	Correct answer text	x	Correct answer text	x	Correct answer text	x
';
$string['importdata_oumultiresponse'] = 'Question name	Question text	Default Correct?	Answer 1	Correct?	Answer 2	Correct?	Answer 3	Correct?	Answer 4	Correct?	Answer 5	Correct?	Answer 6	Correct?	Answer 7	Correct?	Answer 8	Correct?	Answer 9	Correct?
OU MR example 1	This question has 2 correct answers	1	Correct answer text	x	Wrong answer text		Wrong answer text		Wrong answer text		Correct answer text	x
OU MR example 2	This question has 4 correct answers	1	Wrong answer text		Correct answer text	x	Wrong answer text		Correct answer text	x	Wrong answer text		Wrong answer text		Correct answer text	x	Correct answer text	x	Wrong answer text
OU MR example 3	This question has 7 correct answers	1	Correct answer text	x	Correct answer text	x	Correct answer text	x	Correct answer text	x	Correct answer text	x	Correct answer text	x	Correct answer text	x
';
$string['importdata_truefalse'] = 'Question name	Question text	Default mark	True/False
TF example 1	This statement is true.	1	t
TF example 2	This statement is false.	1	f
';
$string['importdata_description'] = 'Import field';
$string['importdata_copypastebutton'] = '<button title="Copy to clipboard" onclick="copyToClipboard()" class="btn btn-secondary float-right" name="copytoclipboard" id="id_copytoclipboard" type="button"><i class="fa fa-clipboard"></i></button>';
$string['importsuccess'] = 'Import successful';
$string['importerror'] = 'Insufficient parameters - skipping question';
$string['importresult'] = 'Result of import';
$string['importtotalsuccess'] = 'Successfully imported questions: ';
$string['importtotalerror'] = 'Errors: ';
$string['infolabel'] = 'Description';
$string['infotext'] = 'With this utility, you can easily create a large number of multiple choice questions in your preferred spreadsheet app, and then import them directly into the Moodle question bank.';
$string['howtouselabel'] = 'How to use';
$string['howtouse'] = '<ol><li>Select a question type from the dropdown (a comparison of different question types can be found <a href="https://moodle.technikum-wien.at/course/view.php?id=5682#multichoice">here</a>)</li>
<li>Copy/paste the contents of the import field into you favourite spreadsheet program (eg MS Excel)</li>
<li>Delete the example questions, fill in as many rows of questions as you want</li>
<li>Copy/paste all the data back from your spreadsheet into the Import field and press <b>Submit</b></li></ol>
The first 3 columns are the same for all question types - Question name, question text and default mark.<br><br>
Shuffling answers is automatically enabled for all questions that support it.';
$string['pluginname'] = 'Mass creation of multiple choice questions';
$string['pluginnameadding'] = '<b>Ma</b>ss <b>cr</b>eation <b>o</b>f <b>mu</b>ltiple <b>cho</b>ice questions';
$string['pluginname_help'] = 'Easy and fast bulk creation of multiple choice questions of different question types';
$string['pluginnamesummary'] = 'Allows easy and fast bulk creation of multiple choice questions of different question types';
$string['privacy:metadata'] = 'The Mass Creation Of Multiple Choice question type plugin does not store any personal data.';
$string['toomanycorrect'] = 'A maximum of 10 correct answers for auto-calculated grades is supported for this question type. Manually enter grades that add up to 100 or use question type "OU multiple response"';
$string['toomanyincorrect'] = 'Grade calculation might be inaccurate for more than 10 incorrect answers, since Moodle provides limited grade options for this question type.';
