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

/**
 * Various helper functions for the plugin.
 *
 * @package local_course_template
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_course_template_helper {
    /**
     * Applies the course template to the given course.
     *
     * @param int $courseid The target course.
     * @return bool A status indicating success or failure
     */
    public static function template_course($courseid) {

        $templatecourseid = self::find_term_template($courseid);
        if ($templatecourseid == false) {
            return;
        }

        // Create and extract template backup file.
        $backupid = \local_course_template_backup::create_backup($templatecourseid);
        if (!$backupid) {
            return false;
        }

        // Restore the backup.
        $status = \local_course_template_backup::restore_backup($backupid, $courseid);
        if (!$status) {
            return false;
        }

        // Cleanup potential news forum duplication.
        self::prune_news_forums($courseid);

        // Trigger custom event.
        $systemcontext = context_system::instance();
        $event = \local_course_template\event\template_copied::create([
            'context' => $systemcontext,
            'other' => [
                'courseid' => $courseid,
                'templateid' => $templatecourseid
            ]
        ]);
        $event->trigger();

        return true;
    }

    /**
     * Locate the term template for the course.
     *
     * @param int $courseid The course.
     * @return int|bool The course it for the template, or false if none found
     */
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
                    $defaulttemplateshortname = get_config('local_course_template', 'defaulttemplate');
                    $defaulttemplatecourse = $DB->get_record('course', array('shortname' => $defaulttemplateshortname));
                    if (!empty($defaulttemplateshortname && !empty($defaulttemplatecourse))) {
                        $cache->set($defaulttemplateshortname, $defaulttemplatecourse->id);
                        return $defaulttemplatecourse->id;
                    }
                    return false;
                } else {
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

    /**
     * Remove news forums created by the template.
     *
     * @param int $courseid the course
     */
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
            $cm = get_coursemodule_from_instance('forum', $forum->id);
            course_delete_module($cm->id);
        }
    }
}
