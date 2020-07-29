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
 * Local course template settings definitions.
 *
 * @package   local_course_template
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_course_template', get_string('pluginname', 'local_course_template'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_configtext('local_course_template/extracttermcode',
        new lang_string('extracttermcode', 'local_course_template'),
        new lang_string('extracttermcode_desc', 'local_course_template'), '', PARAM_NOTAGS));

    $settings->add(new admin_setting_configtext('local_course_template/templatenameformat',
        new lang_string('templatenameformat', 'local_course_template'),
        new lang_string('templatenameformat_desc', 'local_course_template'), 'Template-[TERMCODE]', PARAM_NOTAGS));

    $settings->add(new admin_setting_configtext('local_course_template/defaulttemplate',
        get_string('defaulttemplate', 'local_course_template'),
        get_string('defaulttemplate_desc', 'local_course_template'), '', PARAM_NOTAGS));

    $enableconfig = new admin_setting_configcheckbox('local_course_template/enablecaching',
        new lang_string('enablecaching', 'local_course_template'),
        new lang_string('enablecaching_desc', 'local_course_template'),
    1);
    $enableconfig->set_updatedcallback('local_course_template_update_cache');
    $settings->add($enableconfig);
}