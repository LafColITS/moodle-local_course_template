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

namespace local_course_template;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/filestorage/mbz_packer.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Creates templated course backups and applies settings.
 *
 * @package local_course_template
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup {
    /**
     * Creates a backup for the given course and extracts it to temporary file storage.
     *
     * @param int $courseid The course to backup
     * @return int The courseid
     */
    public static function create_backup($courseid) {
        global $CFG;

        // Try to find the backup.
        $storedfile = self::get_cached_course($courseid);
        if ($storedfile === false) {

            // Instantiate controller.
            $bc = new \backup_controller(
                \backup::TYPE_1COURSE, $courseid, \backup::FORMAT_MOODLE, \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, get_admin()->id);

            // Run the backup.
            $bc->set_status(\backup::STATUS_AWAITING);
            $bc->execute_plan();
            $result = $bc->get_results();
            $bc->destroy();

            // Store the backup.
            $file = $result['backup_destination'];
            $context = \context_course::instance($courseid);
            $timestamp = time();
            $fs = get_file_storage();
            $filerecord = array(
                'contextid' => $context->id,
                'component' => 'local_course_template',
                'filearea' => 'backup',
                'itemid' => $timestamp,
                'filepath' => '/',
                'filename' => 'template_backup.mbz',
                'timecreated' => $timestamp,
                'timemodified' => $timestamp
            );
            $storedfile = $fs->create_file_from_storedfile($filerecord, $file);
            $file->delete();
            self::set_cached_course($context->id, $storedfile);
        }

        // Extract the backup.
        $packer = new \mbz_packer();
        $storedfile->extract_to_pathname($packer, "$CFG->tempdir/backup/$courseid");
        return $courseid;
    }

    /**
     * Returns the cached backup course, if it exists.
     *
     * Returns the cached backup course if it exists, or false it it does not. Also returns
     * false if caching is disabled.
     *
     * @param int $courseid the courseid of the template course
     * @return \stored_file|boolean
     */
    public static function get_cached_course($courseid) {
        $enablecaching = get_config('local_course_template', 'enablecaching');
        if (empty($enablecaching) || $enablecaching == 0) {
            return false;
        }
        $context = \context_course::instance($courseid);
        $cache = \cache::make('local_course_template', 'backups');
        $storedfile = $cache->get($context->id);
        return $storedfile;
    }

    /**
     * Set the cached backup course.
     *
     * Caches the stored file for the backup of the given template course. It does nothing
     * if caching is disabled.
     *
     * @param int $contextid the contextid of the course
     * @param \storedfile $storedfile the stored file of the backup
     */
    public static function set_cached_course($contextid, $storedfile) {
        $enablecaching = get_config('local_course_template', 'enablecaching');
        if (empty($enablecaching) || $enablecaching == 0) {
            return;
        }
        $cache = \cache::make('local_course_template', 'backups');
        $cache->set($contextid, $storedfile);
    }

    /**
     * Takes template course backup and restores it to the given course.
     *
     * @param int $templateid The id of the template course
     * @param int $courseid The id of the target course
     * @return bool A status indicating success or failure
     */
    public static function restore_backup($templateid, $courseid) {
        $admin = get_admin();
        $rc = new \restore_controller(
            $templateid, $courseid, \backup::INTERACTIVE_NO, \backup::MODE_SAMESITE, $admin->id, \backup::TARGET_EXISTING_ADDING);
        self::apply_defaults($rc);
        if (!$rc->execute_precheck(true)) {
            return false;
        }
        $rc->execute_plan();
        $rc->destroy();
        return true;
    }

    /**
     * Apply standing settings to the restore controller before executing the restore.
     *
     * @copyright 2011 Louisiana State University
     * @param restore_controller $rc The restore controller
     */
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
                if ($setting->get_status() == \backup_setting::NOT_LOCKED) {
                    $setting->set_value($value);
                }
            }
        }
    }
}
