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
 * Main block file
 *
 * @package    block
 * @subpackage course_level
 * @copyright  2012 University of London Computer Centre
 * @author     Ian Wild {@link http://moodle.org/user/view.php?id=325899}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Form for editing a Course Level block
 *
 * @copyright 2012 Ian Wild
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_course_level_edit_form extends block_edit_form {
    /**
     * @param MoodleQuickForm $mform
     */
    protected function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_title', get_string('title', 'block_course_level'));
        $mform->setDefault('config_title', get_string('pluginname', 'block_course_level'));
        $mform->setType('config_title', PARAM_MULTILANG);

        $mform->addElement('advcheckbox', 'config_showcode', get_string('showcode', 'block_course_level'));
        $mform->setDefault('config_showcode', 0);

        $options = array(
            block_course_level::TRIM_RIGHT => get_string('trimmoderight', 'block_course_level'),
            block_course_level::TRIM_LEFT => get_string('trimmodeleft', 'block_course_level'),
            block_course_level::TRIM_CENTER => get_string('trimmodecentre', 'block_course_level')
        );
        $mform->addElement('select', 'config_trimmode', get_string('trimmode', 'block_course_level'), $options);
        $mform->setType('config_trimmode', PARAM_INT);

        $mform->addElement('text', 'config_trimlength', get_string('trimlength', 'block_course_level'));
        $mform->setDefault('config_trimlength', 50);
        $mform->setType('config_trimlength', PARAM_INT);

        $mform->addElement('advcheckbox', 'config_showmoodlecourses', get_string('showmoodlecourses', 'block_course_level'));
        $mform->setDefault('config_showmoodlecourses', 0);

        $mform->addElement('text', 'config_admin_tool_url', get_string('admin_tool_url', 'block_course_level'));
        $mform->setDefault('config_admin_tool_url', get_string('default_admin_tool_url', 'block_course_level'));
        $mform->setType('config_admin_tool_url', PARAM_URL);

        $mform->addElement('text', 'config_admin_tool_magic', get_string('admin_tool_magic', 'block_course_level'));
        $mform->setDefault('config_admin_tool_magic', get_string('default_admin_tool_magic', 'block_course_level'));
        $mform->setType('config_admin_tool_magic', PARAM_TEXT);
    }
}