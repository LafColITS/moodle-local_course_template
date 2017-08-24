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
 * @package   local_course_template
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class local_course_template_template_courses_testcase extends advanced_testcase {
    public function test_course_templating() {
        global $DB;

        $this->resetAfterTest(true);

        // Configure the plugin.
        set_config('extracttermcode', '/[A-Za-z0-9\.]+([0-9]{6})/', 'local_course_template');
        set_config('templatenameformat', 'Template-[TERMCODE]', 'local_course_template');

        // Create the template course.
        $tc1 = $this->getDataGenerator()->create_course(
            array(
                'name' => 'Template Course 1',
                'shortname' => 'Template-201610'
            )
        );
        $activity = $this->getDataGenerator()->create_module('label',
            array('course' => $tc1->id));

        $tc2 = $this->getDataGenerator()->create_course(
            array(
                'name' => 'Template Course 2',
                'shortname' => 'Template-201620'
            )
        );
        $activity = $this->getDataGenerator()->create_module('assign',
            array('course' => $tc2->id));
        $this->getDataGenerator()->create_module('forum',
            array('course' => $tc2->id, 'type' => 'news'));
        $this->getDataGenerator()->create_module('forum',
            array('course' => $tc2->id, 'type' => 'news'));

        $c1 = $this->getDataGenerator()->create_course(
            array(
                'idnumber' => '1000.201610'
            )
        );

        $this->assertEquals(2, $DB->count_records('label'));
        $this->assertEquals(1, $DB->count_records('assign'));

        $c2 = $this->getDataGenerator()->create_course(
            array(
                'idnumber' => '1000.201620'
            )
        );

        $this->assertEquals(2, $DB->count_records('label'));
        $this->assertEquals(2, $DB->count_records('assign'));

        // Ensure second news forum is deleted.
        $this->assertEquals(1, $DB->count_records('forum', array('course' => $c2->id)));
        $this->assertEquals(2, $DB->count_records('course_modules', array('course' => $c2->id)));

        $c3 = $this->getDataGenerator()->create_course();

        $this->assertEquals(2, $DB->count_records('label'));
        $this->assertEquals(2, $DB->count_records('assign'));

        // Bulk course creation.
        $category1 = $this->getDataGenerator()->create_category();
        for ($categoryid = 2; $categoryid <= 20; $categoryid++) {
            $category = $this->getDataGenerator()->create_category(array('parent' => $category1->id));
            for ($course = 1; $course <= 10; $course++) {
                $coursenum = ($categoryid * 10) + $course;
                $this->getDataGenerator()->create_course(array('category' => $category->id, 'idnumber' => str_pad($coursenum, 5, '0', STR_PAD_LEFT). '.201610'));
            }
        }
        $this->assertEquals(192, $DB->count_records('label'));
        $this->assertEquals(2, $DB->count_records('assign'));

        $c4 = $this->getDataGenerator()->create_course(
            array(
                'idnumber' => 'XLSB7201610'
            )
        );
        $this->assertEquals(193, $DB->count_records('label'));
        $this->assertEquals(2, $DB->count_records('assign'));

    }
}
