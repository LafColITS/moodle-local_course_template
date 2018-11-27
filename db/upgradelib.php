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
 * Helper functions for the upgrade tasks.
 *
 * @package   local_course_template
 * @copyright 2017 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This function identifies and removed orphaned forum course modules.
 */
function local_course_template_cleanup_modules() {
    global $DB;

    // Get the forum identifier.
    $forumid = $DB->get_field('modules', 'id', array('name' => 'forum'));
    if (!$forumid) {
        return true;
    }

    // Find all the broken modules.
    $orphans = $DB->get_records_sql('SELECT * FROM {course_modules} cm WHERE
        cm.instance NOT IN (SELECT id FROM {forum} f) AND cm.module=?', array($forumid));
    if (empty($orphans)) {
        return true;
    }

    foreach ($orphans as $orphan) {
        // Delete the context.
        context_helper::delete_instance(CONTEXT_MODULE, $orphan->id);

        // Delete the module from the course_modules table.
        $DB->delete_records('course_modules', array('id' => $orphan->id));

        // Delete module from that section.
        delete_mod_from_section($orphan->id, $orphan->section);

        // Rebuild the course cache.
        rebuild_course_cache($orphan->course, true);
    }

    return true;
}
