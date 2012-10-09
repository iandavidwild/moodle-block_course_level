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
 * Cache just the details we need to build a UAL tree as we don't want to be querying the Moodle DB repeatedly. Note that
 * for the UAL project page load times are an issue.
 */
class ual_course {
    /** @var int Used to identify this course as 'unknown' (default) 0 */
    const COURSETYPE_UNKNOWN = 0;
    /** @var int Used to identify this course as a 'programme' 1 */
    const COURSETYPE_PROGRAMME = 1;
    /** @var int Used to identify this course as a 'course', as UAL defines them  1 */
    const COURSETYPE_COURSE = 2;
    /** @var int Used to identify this course as a 'unit' 2 */
    const COURSETYPE_UNIT = 3;

    /** @var int What type of UAL course does this represent? */
    private $type = self::COURSETYPE_UNKNOWN;
    /** @var string This is the Moodle course short name */
    private $shortname = null;
    /** @var string The Moodle course full name */
    private $fullname = null;
    /** @var int The Moodle course id */
    private $id = null;
    /** @var int The id of the parent node. For a 'programme' this will be 0 */
    private $parentid = 0;
    /** @var array An array of all the children this node has */
    private $children = array();

    /**
     * Construct an instance of a UAL course using specified properties
     *
     * @param $properties
     */
    public function __construct($properties) {
        if (is_array($properties)) {
            // Check the array for each property that we allow to set at construction.
            // type         - The type of course this is, i.e. programme, course or unit
            // shortname    - The Moodle course short name
            // fullname     - The Moodle course full name
            // id           - The Moodle course id, which we can query if we need to but we don't want to make this too slow.
            // parentid     - The Moodle course id of the parent. Again, we can query this if we want to.

            if (array_key_exists('type', $properties)) {
                $this->type = $properties['type'];
            }
            if (array_key_exists('shortname', $properties)) {
                $this->shortname = $properties['shortname'];
            }
            if (array_key_exists('fullname', $properties)) {
                $this->fullname = $properties['fullname'];
            }
            if (array_key_exists('id', $properties)) {
                $this->id = $properties['id'];
            }
            if (array_key_exists('parentid', $properties)) {
                $this->parentid = $properties['parentid'];
            }
        } else {
            throw new coding_exception('You must set the properties of a new instance of a \'ual_course\' object when you create it.');
        }
    }

    /**
     * Return the course id.
     *
     * @return int|null
     */
    public function get_id() {
        return ($this->id);
    }

    /**
     * Return the course id of the parent course.
     *
     * @return int
     */
    public function get_parentid() {
        return ($this->parentid);
    }

    /**
     * Return the course fullname.
     *
     * @return null|string
     */
    public function get_fullname() {
        return ($this->fullname);
    }

    /**
     * Return the course shortname.
     *
     * @return null|string
     */
    public function get_shortname() {
        return ($this->shortname);
    }

    /**
     * Specify the course id - typically the Moodle course id.
     *
     * @param $id
     */
    public function set_id($id) {
        $this->id = $id;
    }

    /**
     * Specify the parent course id - typically this will be a Moodle course id.
     *
     * @param $parentid
     */
    public function set_parentid($parentid) {
        $this->parentid = $parentid;
    }

    /**
     * Specify the course full name - typically the Moodle course full name
     *
     * @param $fullname
     */
    public function set_fullname($fullname) {
        $this->fullname = $fullname;
    }

    /**
     * Specify the course short name - typically the Moodle course full name
     *
     * @param $shortname
     */
    public function set_shortname($shortname) {
        $this->shortname = $shortname;
    }

    /**
     * Force this course to adopt the specified course. Not sure if it is, philosophically speaking, correct to *make*
     * a parent adopt a child?
     *
     * @param $node
     */
    public function adopt_child($node) {
        $this->children[] = $node;
    }

    /**
     * Return all of this course's children.
     *
     * @return array
     */
    public function get_children() {
        return $this->children;
    }
}

// TODO abstract this and make it non-UAL course specific?
/**
 * This class creates a UAL-specific course level tree.
 */
class course_level_tree implements renderable {
    public $context;
    public $courses;
    public function __construct() {
        global $USER;
        $this->context = get_context_instance(CONTEXT_USER, $USER->id);

        // Build course level tree here...
        $this->courses = $this->get_courses_tree();
    }

    // TODO This needs to be in the same lib as ual_course declaration

    private function get_ual_courses() {
        // Should be a set of DEB queries but, for now, let's construct something manually.

        // Implement the following tree:
        //
        // programme 1
        //  |- course 1
        //        |- unit 1
        //        |- unit 2
        //  |- course 2
        //        |- unit 3
        //

        // Moodle courses must be specified in their logical order so that we can render them properly...
        $programme1 = new ual_course( array("type" => ual_course::COURSETYPE_PROGRAMME, "shortname" => "Science", "fullname" => "LCF Science Programme (programme level)", "id" => 3) );
        $course1 = new ual_course( array("type" => ual_course::COURSETYPE_COURSE, "shortname" => "FdSC Beauty And Spa Management", "fullname" => "LCF FdSC Beauty And Spa Management FT (course level)", "id" => 6, "parentid" => 3 ) );
        $unit1 = new ual_course( array("type" => ual_course::COURSETYPE_UNIT, "shortname" => "LCF FdSC Beauty And Spa Management FT Yr1", "fullname" => "20171F311_Q11-12 LCF FdSC Beauty And Spa Management FT Yr1", "id" => 7, "parentid" => 6) );
        $unit2 = new ual_course( array("type" => ual_course::COURSETYPE_UNIT, "shortname" => "LCF FdSC Beauty And Spa Management FT Yr2 (Yr and Mode level)", "fullname" => "20171F312_Q11-12 LCF FdSC Beauty And Spa Management FT Yr2", "id" => 10, "parentid" => 6) );
        $course2 = new ual_course( array("type" => ual_course::COURSETYPE_COURSE, "shortname" => "LCF BSc (Hons) Cosmetic Science (course level)", "fullname" => "LCF BSc (Hons) Cosmetic Science (course level)", "id" => 8, "parentid" => 3) );
        $unit3 = new ual_course( array("type" => ual_course::COURSETYPE_UNIT, "shortname" => "LCF BSc (Hons) Cosmetic Science FT Yr1 (Yr and Mode level)", "fullname" => "LCF BSc (Hons) Cosmetic Science FT Yr1 (Yr and Mode level)", "id" => 9, "parentid" => 8) );

        // Add these courses to a return array...
        $result = array();
        $result[3] = $programme1;
        $result[6] = $course1;
        $result[7] = $unit1;
        $result[10] = $unit2;
        $result[8] = $course2;
        $result[9] = $unit3;

        return $result;
    }

    /**
     * Returns array based tree structure of courses. I'm trying not to make this recursive so that it is as quick as possible.
     * That said, it has nested loops, which I'm not entirely convinced about :-)
     *
     * @return array each course represented by coursename, subcourses, units and dirfile array elements
     */
    private function get_courses_tree() {
        $dataset = $this->get_ual_courses();
        // first create directory structure

        $tree = array();
        foreach ($dataset as $thisid=>&$node) {
            if ($node->get_parentid() == 0) {
                $tree[$thisid] = &$node;
            } else {
                $parentid = $node->get_parentid();
                $dataset[$parentid]->adopt_child($node);
            }
        }

        return $tree;
    }
}

?>