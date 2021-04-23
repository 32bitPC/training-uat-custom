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
 * Cohort related management functions, this file needs to be included manually.
 *
 * @package    core_cohort
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../config.php');
require($CFG->dirroot.'/cohort/lib.php');
require($CFG->dirroot.'/phlcohort/lib.php');
require_once($CFG->libdir.'/adminlib.php');

require($CFG->dirroot.'/phlcohort/edit_form.php');
require_once($CFG->dirroot . '/lib/coursecatlib.php');



$cohortid=optional_param('id',0, PARAM_INT);
$confirm=optional_param('confirm',0, PARAM_INT);
$trainer=optional_param('trainer','', PARAM_RAW);

require_login();

$contextid=0;
if ($contextid) {
    $context = context::instance_by_id($contextid, MUST_EXIST);
} else {
    $context = context_system::instance();
}

if ($context->contextlevel != CONTEXT_COURSECAT and $context->contextlevel != CONTEXT_SYSTEM) {
    print_error('invalidcontext');
}

$category = null;
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = $DB->get_record('course_categories', array('id'=>$context->instanceid), '*', MUST_EXIST);
}

$manager = has_capability('moodle/cohort:manage', $context);
$canassign = has_capability('moodle/cohort:assign', $context);
if (!$manager) {
    ;//require_capability('moodle/cohort:view', $context);
}

$strcohorts = get_string('cohorts', 'cohort');

/*
if ($category) {
    $PAGE->set_pagelayout('admin');
    $PAGE->set_context($context);
    $PAGE->set_url('/phlcohort/index.php', array('contextid'=>$context->id));
    $PAGE->set_title($strcohorts);
    $PAGE->set_heading($COURSE->fullname);
    $showall = false;
} else {
    admin_externalpage_setup('cohorts', '', null, '', array('pagelayout'=>'report'));
}
*/

$PAGE->set_context($context);
$PAGE->set_url('/phlcohort/index.php', array('contextid'=>$context->id));
echo $OUTPUT->header();


$cohortphl = $DB->get_record('cohortphl', array('cohortid'=>$cohortid), '*', MUST_EXIST);


global $USER;
$cohorts = cohort_get_phl_cohort_detail($cohortid);

$cohort=null;
foreach ($cohorts as $c) {    
    $cohort=$c;
}
redirect(new moodle_url("/phlcohort/excel_reader/PHPExcel/download.php?context=members&id=$cohortid&trainer=$cohortphl->trainer"));
echo $OUTPUT->footer();

