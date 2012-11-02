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
 * This class creates a course level tree. It shows the relationship between Moodle courses - which will be specific
 * to a given institution.
 */

// Fix bug: moving the block means the ual_api local plugin is no longer loaded. We'll need to specify the path to
// the lib file directly. See https://moodle.org/mod/forum/discuss.php?d=197997 for more information...
require_once($CFG->dirroot . '/local/ual_api/lib.php');

class course_level_tree implements renderable {
    public $context;
    public $courses;
    public function __construct() {
        global $USER, $CFG;
        $this->context = get_context_instance(CONTEXT_USER, $USER->id);

        // Is ual_mis class loaded?
        if (class_exists('ual_mis')) {
            $mis = new ual_mis();

            $ual_username = $mis->get_ual_username($USER->username);

            $tree = $mis->get_user_courses_tree($ual_username);

            $this->courses = $this->construct_view_tree($tree);
        }

        // TODO warn if local plugin 'ual_api' is not installed.
    }

    private function construct_view_tree($tree) {
        // The $tree is an array. This function necessarily converts this into a multidimentional array...

        foreach($tree as $key=>$node) {
            $node_shortname = $node->get_shortname();
            if(preg_match('/^[0-9]/', $node_shortname)) { // Then this is a course not a unit
                // Cache the node's children...
                $units = $node->get_children();

                // Then get the node to abandon them...
                $node->abandon_children();

                // Collect units into years...
                $years = array();

                foreach ($units as $unit) {
                    // Get 7th character from the left...
                    // TODO String functions are horribly inefficient so we might want to take a look at this.
                    $shortname = $unit->get_shortname();
                    $year = intval(substr($shortname, -7, 1));

                    $years[$year][] = $unit;
                }

                if(!empty($years)) {
                    foreach($years as $year=>$years_units) {
                        $yearpage = new ual_course(array('shortname' => get_string('year', 'block_course_level').' '.$year,
                                                         'fullname' => get_string('year', 'block_course_level').' '.$year,
                                                         'id' => 0));
                        $node->adopt_child($yearpage);

                        $year_homepage_title = get_string('year', 'block_course_level').' '.$year.' '.get_string('homepage', 'block_course_level');
                        $year_homepage = new ual_course(array('shortname' => $year_homepage_title,
                                                              'fullname' => $year_homepage_title,
                                                                'id' => $node->get_id()));

                        foreach($years_units as $year_unit) {
                            $yearpage->adopt_child($year_unit);
                        }
                    }
                }

                // Now set the id to 0 so the course isn't displayed as a link in the tree...
                $node->set_id(0);

                $node->push_child($year_homepage);
            } else {
                // Remove the reference to this node from the $tree
                unset($tree[$key]);
            }
        }

        return $tree;
    }
}