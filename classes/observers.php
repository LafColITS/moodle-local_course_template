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
 * Observer functions for the plugin.
 *
 * @package    local_course_template
 * @copyright  2016 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_template;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer functions for the plugin.
 *
 * @package local_course_template
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observers {
    /**
     * Attach the template function to new course creations.
     *
     * @param \core\event\course_created $event the course creation event
     */
    public static function course_created(\core\event\course_created $event) {
        if (empty($event->objectid)) {
            return;
        }
        $lockfactory = \core\lock\lock_config::get_lock_factory('local_course_template_course_created');
        $lockkey = "course{$event->objectid}";
        $lock = $lockfactory->get_lock($lockkey, 0);
        if ($lock !== false) {
            helper::template_course($event->objectid);
            $lock->release();
        }
    }
}
