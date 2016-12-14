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
 * Helper functions.
 *
 * @package   local_course_template
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

class local_course_template_backup {
    public static function create_backup($courseid) {
        global $CFG;

        // Instantiate controller.
        $bc = new backup_controller(
            \backup::TYPE_1COURSE, $courseid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_GENERAL, 2);

        // Run the backup.
        $bc->set_status(backup::STATUS_AWAITING);
        $bc->execute_plan();
        $result = $bc->get_results();

        // Extract the backup.
        $packer = new mbz_packer();
        $result['backup_destination']->extract_to_pathname($packer, "$CFG->tempdir/backup/9");
        $bc->destroy();
        return $courseid;
    }

    public static function restore_backup($templateid, $courseid) {
        $rc = new restore_controller(
            $templateid, $courseid, backup::INTERACTIVE_NO, backup::MODE_SAMESITE, 2, backup::TARGET_EXISTING_ADDING);
        self::apply_defaults($rc);
        if (!$rc->execute_precheck(true)) {
            return false;
        }
        $rc->execute_plan();
        $rc->destroy();
        return true;
    }

    protected static function apply_defaults($rc) {
        $settings = array(
            'enrol_migratetomanual' => 0,
            'users' => 0,
            'user_files' => 0,
            'role_assignments' => 0,
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1,
            'comments' => 0,
            'userscompletion' => 0,
            'logs' => 0,
            'grade_histories' => 0,
            'keep_roles_and_enrolments' => 0,
            'keep_groups_and_groupings' => 0,
            'overwrite_conf' => 0
        );
        foreach ($settings as $name => $value) {
            if ($rc->get_plan()->setting_exists($name)) {
                $setting = $rc->get_plan()->get_setting($name);
                if ($setting->get_status() == backup_setting::NOT_LOCKED) {
                    $setting->set_value($value);
                }
            }
        }
    }
}
