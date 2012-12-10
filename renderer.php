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
 * Print course level tree
 *
 * @package    block_course_level
 * @copyright  2012 University of London Computer Centre
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/blocks/course_level/lib.php');

class block_course_level_renderer extends plugin_renderer_base {

    private $showcode = 0;
    private $showmoodlecourses = 0;
    private $trimmode = block_course_level::TRIM_RIGHT;
    private $trimlength = 50;
    private $courseid = 0;

    /**
     * Prints course level tree view
     * @return string
     */
    public function course_level_tree($showcode, $trimmode, $trimlength, $courseid, $showmoodlecourses) {
        $this->showcode = $showcode;
        $this->showmoodlecourses = $showmoodlecourses;
        $this->trimmode = $trimmode;
        $this->trimlength = $trimlength;
        $this->courseid = $courseid;

        return $this->render(new course_level_tree);
    }

    /**
     * provides the html contained in the course level block - including the tree itself and the links at the bottom
     * of the block to 'all courses' and 'all programmes'.
     *
     * @param render_course_level_tree $tree
     * @return string
     */
    public function render_course_level_tree(course_level_tree $tree) {
        global $CFG;

        if (empty($tree) ) {
            $html = $this->output->box(get_string('nocourses', 'block_course_level'));
        } else {

            $htmlid = 'course_level_tree_'.uniqid();
            $this->page->requires->js_init_call('M.block_course_level.init_tree', array(false, $htmlid, $CFG->wwwroot.'/course/view.php?id='.$this->courseid));
            $html = '<div id="'.$htmlid.'">';
            $html .= $this->htmllize_tree($tree->courses);
            $html .= '</div>';
        }

        // Do we display courses that the user is enrolled on in Moodle but not enrolled on them according to the IDM data?
        if($this->showmoodlecourses && !empty($tree->moodle_courses)) {
            $html .= html_writer::empty_tag('hr');

            $orphaned_courses = html_writer::start_tag('ul', array('class' => 'orphaned'));
            foreach($tree->moodle_courses as $course) {
                $courselnk = $CFG->wwwroot.'/course/view.php?id='.$course->id;
                $linkhtml = html_writer::link($courselnk,$course->fullname, array('class' => 'orphaned_course'));
                $orphaned_courses .= html_writer::tag('li', $linkhtml);
            }
            $orphaned_courses .= html_writer::end_tag('ul');

            $html .= $orphaned_courses;
        }

        // Add 'View all courses' link to bottom of block...
        $html .= html_writer::empty_tag('hr');
        $viewcourses_lnk = $CFG->wwwroot.'/blocks/course_level/view.php?id='.$this->courseid.'&tab=0';
        $attributes = array('class' => 'view-all');
        $span = html_writer::tag('span', '');
        $html .= html_writer::link($viewcourses_lnk, get_string('view_all_courses', 'block_course_level').$span, $attributes);

        return $html;
    }

    /**
     * Converts the course tree into something more meaningful.
     *
     * @param $tree
     * @param int $indent
     * @return string
     */
    protected function htmllize_tree($tree, $indent=0) {
        global $CFG;

        $yuiconfig = array();
        $yuiconfig['type'] = 'html';

        $result = '<ul>';

        if (!empty($tree)) {
            foreach ($tree as $node) {

                // Does this node have any children?
                $children = $node->get_children();

                // Determine the name of this node
                $name = $this->trim($node->get_fullname());
                if($this->showcode == 1) {
                    $name .= ' ('.$node->get_idnumber().')';
                }

                // Fix to bug UALMOODLE-58: look for ampersand in fullname and replace it with entity
                $name = preg_replace('/&(?![#]?[a-z0-9]+;)/i', "&amp;$1", $name);

                $name = $this->trim($name);
                $node_type = $node->get_type();

                $type_class = 'unknown';

                switch($node_type) {
                    case ual_course::COURSETYPE_PROGRAMME:
                        $type_class = 'programme';
                        break;
                    case ual_course::COURSETYPE_ALLYEARS:
                        $type_class = 'course_all_years';
                        break;
                    case ual_course::COURSETYPE_COURSE:
                        $type_class = 'course';
                        break;
                    case ual_course::COURSETYPE_UNIT:
                        $type_class = 'unit';
                        break;
                }


                $attributes = array();

                // Insert a span tag to allow us to insert an arrow...
                $span = html_writer::tag('span', '');

                if ($children == null) {
                    $attributes['title'] = $name;

                    if($node->get_user_enrolled() == true) {
                        $moodle_url = $CFG->wwwroot.'/course/view.php?id='.$node->get_moodle_course_id();
                        $content = html_writer::link($moodle_url, $name, $attributes);
                    } else {
                        // Display the name but it's not clickable...
                        // TODO make this a configuration option...
                        $content = html_writer::tag('i', $name, $attributes);
                    }
                    $result .= html_writer::tag('li', $content, array('yuiConfig'=>json_encode($yuiconfig), 'class' => $type_class));
                } else {
                    // This is an expandable node...
                    $content = html_writer::tag('div', $name.$span, $attributes);

                    if($indent != 0) {
                        $attributes['class'] = 'expanded';
                    }


                    $result .= html_writer::tag('li', $content.$this->htmllize_tree($children, $indent+1), array('yuiConfig'=>json_encode($yuiconfig), 'class' => $type_class));
                }
            }
        }
        $result .= '</ul>';

        return $result;
    }

    /**
     * Trims the text and shorttext properties of this node and optionally
     * all of its children.
     *
     * @param string $text The text to truncate
     * @return string
     */
    private function trim($text) {
        $result = $text;

        switch ($this->trimmode) {
            case block_course_level::TRIM_RIGHT :
                if (textlib::strlen($text)>($this->trimlength+3)) {
                    // Truncate the text to $long characters.
                    $result = textlib::substr($text, 0, $this->trimlength).'...';
                }
                break;
            case block_course_level::TRIM_LEFT :
                if (textlib::strlen($text)>($this->trimlength+3)) {
                    // Truncate the text to $long characters.
                    $result = '...'.textlib::substr($text, textlib::strlen($text)-$this->trimlength, $this->trimlength);
                }
                break;
            case block_course_level::TRIM_CENTER :
                if (textlib::strlen($text)>($this->trimlength+3)) {
                    // Truncate the text to $long characters.
                    $length = ceil($this->trimlength/2);
                    $start = textlib::substr($text, 0, $length);
                    $end = textlib::substr($text, textlib::strlen($text)-$this->trimlength);
                    $result = $start.'...'.$end;
                }
                break;
        }
        return $result;
    }
}


