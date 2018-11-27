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
 * Define upgrade tasks for the plugin.
 *
 * @package   local_course_template
 * @copyright 2017 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function for plugin.
 *
 * @param int $oldversion The old version of the plugin
 * @return bool A status indicating success or failure
 */
function xmldb_local_course_template_upgrade($oldversion) {
    global $CFG;

    require_once($CFG->dirroot . '/local/course_template/db/upgradelib.php');

    if ($oldversion < 2017082400) {
        // Remediate bug where forums were deleted but the course modules weren't.
        local_course_template_cleanup_modules();
        upgrade_plugin_savepoint(true, 2017082400, 'local', 'course_template');
    }

    return true;
}
