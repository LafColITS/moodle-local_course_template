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
 * Custom event with descriptive log message.
 *
 * @package    local_course_template
 * @copyright  2020 onwards Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_template\event;

defined('MOODLE_INTERNAL') || die();

/**
 * Custom event class to record a template course being copied/used.
 *
 * @copyright  2020 onwards Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class template_copied extends \core\event\base {

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() : string {
        $templateid = $this->data['other']['templateid'];
        $courseid = $this->data['other']['courseid'];
        return get_string(
            'event:template_copied:description',
            'local_course_template',
            ['courseid' => $courseid, 'templateid' => $templateid]
        );
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() : string {
        return get_string('event:template_copied:name', 'local_course_template');
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
}