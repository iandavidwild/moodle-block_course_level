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
 * View all courses and programmes.
 *
 * @package    block
 * @subpackage course_level
 * @copyright  2012 University of London Computer Centre
 * @author     Ian Wild {@link http://moodle.org/user/view.php?id=81450}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//  Lists all the courses and programmes available on the site

require_once('../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->libdir.'/filelib.php');

define('COURSE_SMALL_CLASS', 20);   // Below this is considered small
define('COURSE_LARGE_CLASS', 200);  // Above this is considered large
define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);

$page         = optional_param('page', 0, PARAM_INT);                     // which page to show
$perpage      = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);  // how many per page
$search       = optional_param('search','',PARAM_RAW);                    // make sure it is processed with p() or s() when sending to output!

$contextid    = optional_param('contextid', 0, PARAM_INT);                // one of this or
$courseid     = optional_param('id', 0, PARAM_INT);                       // this are required

$PAGE->set_url('/blocks/course_level/view.php', array(
    'page' => $page,
    'perpage' => $perpage,
    'search' => $search,
    'contextid' => $contextid,
    'id' => $courseid));

// Make sure the context is right so 1) the user knows where they are, 2) the theme renders correctly.
if ($contextid) {
    $context = get_context_instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSE) {
        print_error('invalidcontext');
    }
    $course = $DB->get_record('course', array('id'=>$context->instanceid), '*', MUST_EXIST);
} else {
    $course = $DB->get_record('course', array('id'=>$courseid), '*', MUST_EXIST);
    $context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);
}
// Not needed anymore
unset($contextid);
unset($courseid);

require_login($course);

$systemcontext = get_context_instance(CONTEXT_SYSTEM);

// Maybe it's accessed from the front paget???
$isfrontpage = ($course->id == SITEID);
$frontpagectx = get_context_instance(CONTEXT_COURSE, SITEID);

if ($isfrontpage) {
    $PAGE->set_pagelayout('admin');
    // TODO capability check?
} else {
    $PAGE->set_pagelayout('incourse');
    // TODO capability check?
}

$PAGE->set_title("$course->shortname: ".get_string('display_all', 'block_course_level'));
$PAGE->set_heading(get_string('display_all', 'block_course_level'));
$PAGE->set_pagetype('course-view-' . $course->format);
$PAGE->add_body_class('path-block-course-level-display-all'); // So we can style it independently

echo $OUTPUT->header();

echo '<div class="courselist">';

// Should use this variable so that we don't break stuff every time a variable is added or changed.
$baseurl = new moodle_url('/blocks/course_level/view.php', array(
    'contextid' => $context->id,
    'id' => $course->id,
    'perpage' => $perpage,
    'search' => s($search)));

/// Print settings and things in a table across the top

$controlstable = new html_table();
// TODO include 'search' here
// TODO include 'Filter courses A-Z' here
echo html_writer::table($controlstable);

/// Define a table showing a list of all courses
// Note: 'fullname' is treated as special in a flexible_table. Call the column 'course_fullname' instead.
$tablecolumns = array('shortname', 'course_fullname', 'home', 'units');
$tableheaders = array(get_string('shortname', 'block_course_level'), get_string('fullname', 'block_course_level'),
                        get_string('link', 'block_course_level'), get_string('units', 'block_course_level'));

$table = new flexible_table('block-course-level-display-all-'.$course->id);
$table->define_columns($tablecolumns);
$table->define_headers($tableheaders);
$table->define_baseurl($baseurl->out());

$table->sortable(true, 'shortname', SORT_ASC);
$table->sortable(true, 'course_fullname', SORT_ASC);
// Set 'no_sorting' options if necessary... e.g.
$table->no_sorting('home');
$table->no_sorting('units');

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'display_all');
$table->set_attribute('class', 'generaltable generalbox');

$table->set_control_variables(array(
    TABLE_VAR_SORT    => 'ssort',
    TABLE_VAR_HIDE    => 'shide',
    TABLE_VAR_SHOW    => 'sshow',
    TABLE_VAR_IFIRST  => 'sifirst',
    TABLE_VAR_ILAST   => 'silast',
    TABLE_VAR_PAGE    => 'spage'
));
$table->setup();

// Get all courses from Moodle
// TODO getting all programmes, courses and units is the responsibility of ual_mis. Will need to be moved to local-ual_mis eventually.
$allcourses = get_courses("all");

$totalcount = count($allcourses);

if (!empty($search)) {
    // TODO some searching will need to be done in the result.
}

// we aren't worried about matching courses to some constraint so make...
$matchcount = $totalcount;

$table->initialbars(true);

$table->pagesize($perpage, $matchcount);

if ($sql_sort = $table->get_sql_sort()) {
    // Replace 'course_fullname' with 'fullname'
    $sql_sort = preg_replace('/course_fullname/', 'fullname', $sql_sort);
    $sort = ' ORDER BY '.$sql_sort;
} else {
    $sort = '';
}

// list of courses at the current visible page - paging makes it relatively short
// TODO this will need to be part of ual_mis implementation??? - Need to build the SQL that performs the select
//$courselist = $DB->get_recordset_sql("$select $from $where $sort", $params, $table->get_page_start(), $table->get_page_size());
$courselist = $DB->get_recordset_sql("SELECT * FROM  mdl_course {$sort}", NULL, $table->get_page_start(), $table->get_page_size());


if ($totalcount < 1) {
    echo $OUTPUT->heading(get_string('nothingtodisplay'));
} else {

    if ($matchcount > 0) {
        $coursesprinted = array();

        foreach ($courselist as $course) {
            if (in_array($course->id, $coursesprinted)) {
                continue;
            }
            $data = array();

            $data[] = $course->shortname;
            $data[] = $course->fullname;

            $link = html_writer::link(new moodle_url('/course/view.php?id='.$course->id), get_string('course_home','block_course_level'));

            $data[] = $link;

            // TODO Link to page displaying course units??? Ask if this can be a popup???
            $data[] = html_writer::tag('p', get_string('years_and_units', 'block_course_level'));

            $table->add_data($data);
        }

        $table->print_html();
    }
}

$perpageurl = clone($baseurl);
$perpageurl->remove_params('perpage');
if ($perpage == SHOW_ALL_PAGE_SIZE) {
    $perpageurl->param('perpage', DEFAULT_PAGE_SIZE);
    echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showperpage', '', DEFAULT_PAGE_SIZE)), array(), 'showall');

} else if ($matchcount > 0 && $perpage < $matchcount) {
    $perpageurl->param('perpage', SHOW_ALL_PAGE_SIZE);
    echo $OUTPUT->container(html_writer::link($perpageurl, get_string('showall', '', $matchcount)), array(), 'showall');
}

echo '</div>';  // courselist

echo $OUTPUT->footer();

if ($courselist) {
    $courselist->close();
}

?>