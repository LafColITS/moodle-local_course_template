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

class local_course_template_helper {
    public static function template_course($courseid) {
        global $CFG;

        $templatecourseid = self::find_term_template($courseid);
        if ($templatecourseid == false) {
            return;
        }

        $bc = new backup_controller(
            backup::TYPE_1COURSE, $templatecourseid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_SAMESITE, 2);
        $bc->execute_plan();
        $backupfile = $bc->get_results();
        $packer = new mbz_packer();

        $backupfile['backup_destination']->extract_to_pathname($packer, "$CFG->tempdir/backup/9");
        $bc->destroy();
        $restore = new restore_controller(
                $templatecourseid, $courseid, backup::INTERACTIVE_NO, backup::MODE_SAMESITE, 2, backup::TARGET_EXISTING_ADDING);
        self::apply_defaults($restore);
        if (!$restore->execute_precheck('true')) {
            return false;
        }
        $restore->execute_plan();
        $restore->destroy();
        self::prune_news_forums($courseid);
    }

    protected static function find_term_template($courseid) {
        global $DB;

        // Don't continue if there's no pattern.
        $pattern = get_config('local_course_template', 'extracttermcode');
        if (empty($pattern)) {
            return false;
        }

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
        $subject = $course->idnumber;
        preg_match($pattern, $subject, $matches);
        if (!empty($matches) && count($matches) >= 2) {
            $templateshortname = str_replace('[TERMCODE]', $matches[1],
                get_config('local_course_template', 'templatenameformat'));

            // Check if the idnumber is cached.
            $cache = cache::make('local_course_template', 'templates');
            $templatecourseid = $cache->get($templateshortname);
            if ($templatecourseid == false) {
                $templatecourse = $DB->get_record('course', array('shortname' => $templateshortname));
                if (empty($templatecourse)) {
                    // No template found.
                    return false;
                } else {
                    $cache->set($templateshortname, $templatecourse->id);
                    return $templatecourse->id;
                }
            } else {
                return $templatecourseid;
            }
        } else {
            // This course doesn't conform to the given naming convention, so skip.
            return false;
        }
    }

    protected static function prune_news_forums($courseid) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/mod/forum/lib.php");

        $newsforums = $DB->get_records('forum', array('course' => $courseid, 'type' => 'news'),
            'id ASC', 'id');
        if (count($newsforums) <= 0) {
            return;
        }
        array_shift($newsforums);
        foreach ($newsforums as $forum) {
            forum_delete_instance($forum->id);
        }
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
