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
 * Unit tests for course module cleanup on upgrade.
 *
 * @package   local_course_template
 * @copyright 2017 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/course_template/db/upgradelib.php');

/**
 * Unit tests covering course module cleanup on upgrade.
 *
 * @package local_course_template
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_course_template_upgradelib_testcase extends advanced_testcase {
    /**
     * Test that vestigial news forum course modules are cleaned up.
     */
    public function test_upgradelib() {
        global $DB;
        $this->resetAfterTest(true);

        // Create three courses.
        $c1 = $this->getDataGenerator()->create_course();
        $c2 = $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_course();

        // Add a news forum to each.
        $this->getDataGenerator()->create_module('forum',
            array('course' => $c1->id, 'type' => 'news'));
        $f2 = $this->getDataGenerator()->create_module('forum',
            array('course' => $c2->id, 'type' => 'news'));
        $f3 = $this->getDataGenerator()->create_module('forum',
            array('course' => $c2->id, 'type' => 'news'));

        // Add a normal forum to each.
        $this->getDataGenerator()->create_module('forum',
            array('course' => $c1->id));
        $this->getDataGenerator()->create_module('forum',
            array('course' => $c2->id));
        $this->getDataGenerator()->create_module('forum',
            array('course' => $c2->id));

        // Sanity check.
        $this->assertEquals(6, $DB->count_records('forum'));
        $this->assertEquals(6, $DB->count_records('course_modules'));

        // Delete two news forums the wrong way.
        forum_delete_instance($f2->id);
        forum_delete_instance($f3->id);

        // Confirm things are bad.
        $this->assertEquals(4, $DB->count_records('forum'));
        $this->assertEquals(6, $DB->count_records('course_modules'));

        // Run cleanup task.
        local_course_template_cleanup_modules();

        // Confirm things are better.
        $this->assertEquals(4, $DB->count_records('forum'));
        $this->assertEquals(4, $DB->count_records('course_modules'));
    }
}
