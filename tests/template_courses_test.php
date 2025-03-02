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
 * Unit tests for the plugin.
 *
 * @package   local_course_template
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_template;

/**
 * Unit tests covering course template creation.
 *
 * @package local_course_template
 * @copyright 2016 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class template_courses_test extends \advanced_testcase {
    /**
     * Find course templates and apply them to new courses.
     */
    public function test_course_templating(): void {
        global $DB;

        $this->resetAfterTest(true);

        // Enable logging.
        $this->preventResetByRollback();
        set_config('enabled_stores', 'logstore_standard', 'tool_log');
        set_config('buffersize', 0, 'logstore_standard');

        // Configure the plugin.
        set_config('extracttermcode', '/[A-Za-z0-9\.]+([0-9]{6})/', 'local_course_template');
        set_config('templatenameformat', 'Template-[TERMCODE]', 'local_course_template');
        set_config('defaulttemplate', 'default-template', 'local_course_template');

        // Create the template courses.
        $tc1 = $this->getDataGenerator()->create_course(
            [
                'name' => 'Template Course 1',
                'shortname' => 'Template-201610',
                'startdate' => '1693227600',
                'enddate' => '1702303200',
            ]
        );
        $activity = $this->getDataGenerator()->create_module('label',
            ['course' => $tc1->id]);

        $tc2 = $this->getDataGenerator()->create_course(
            [
                'name' => 'Template Course 2',
                'shortname' => 'Template-201620',
                'startdate' => '1704376800',
                'enddate' => '1706191200',
            ]
        );
        $activity = $this->getDataGenerator()->create_module('assign',
            ['course' => $tc2->id]);
        $this->getDataGenerator()->create_module('forum',
            ['course' => $tc2->id, 'type' => 'news']);
        $this->getDataGenerator()->create_module('forum',
            ['course' => $tc2->id, 'type' => 'news']);

        // Course matching 201610 template.
        $c1 = $this->getDataGenerator()->create_course(
            [
                'idnumber' => '1000.201610',
            ]
        );

        $this->assertEquals(2, $DB->count_records('label'));
        $this->assertEquals(1, $DB->count_records('assign'));

        // Check logging.
        $logs = $DB->get_records('logstore_standard_log', ['component' => 'local_course_template']);
        $this->assertEquals(1, count($logs));
        $log = $logs[array_keys($logs)[0]];
        $this->assertEquals('copied', $log->action);
        $this->assertEquals('\local_course_template\event\template_copied', $log->eventname);
        $this->assertEquals('c', $log->crud);
        $other = @unserialize($log->other) ? unserialize($log->other) : json_decode($log->other, true);
        $this->assertEquals($c1->id, $other['courseid']);
        $this->assertEquals($tc1->id, $other['templateid']);

        // Course matching 201620 template.
        $c2 = $this->getDataGenerator()->create_course(
            [
                'idnumber' => '1000.201620',
            ]
        );

        // Course matching 201620 has resources.
        $this->assertEquals(2, $DB->count_records('label'));
        $this->assertEquals(2, $DB->count_records('assign'));

        // Course matching 201620 has own start/end date date.
        $c2 = get_course($c2->id);
        $this->assertEquals(date("Y-m-d", time()), date("Y-m-d", $c2->startdate));
        $this->assertEquals(0, $c2->enddate);

        // Check logging.
        $logs = $DB->get_records('logstore_standard_log', ['component' => 'local_course_template']);
        $this->assertEquals(2, count($logs));
        $log = end($logs);
        $other = @unserialize($log->other) ? unserialize($log->other) : json_decode($log->other, true);
        $this->assertEquals($c2->id, $other['courseid']);
        $this->assertEquals($tc2->id, $other['templateid']);

        // Ensure second news forum is deleted.
        $this->assertEquals(1, $DB->count_records('forum', ['course' => $c2->id]));
        $this->assertEquals(2, $DB->count_records('course_modules', ['course' => $c2->id]));

        // Course matching termcode regex, but not matching a template.
        // There's no default right now, so this should NOT be based on a template.
        $this->assertEquals(0, $DB->count_records('url'));
        $c3 = $this->getDataGenerator()->create_course(
            [
                'idnumber' => 'XLSB7201630',
            ]
        );
        $this->assertEquals(0, $DB->count_records('url'));
        $this->assertEquals(2, $DB->count_records('assign'));
        $this->assertEquals(date("Y-m-d", time()), date("Y-m-d", $c3->startdate));
        $this->assertEquals(0, $c3->enddate);

        // Check logging.
        $logs = $DB->get_records('logstore_standard_log', ['component' => 'local_course_template']);
        $this->assertEquals(2, count($logs));

        // Create default template course.
        $tcd = $this->getDataGenerator()->create_course(
            [
                'name' => 'Default Template Course',
                'shortname' => 'default-template',
            ]
        );
        $activity = $this->getDataGenerator()->create_module('url',
            ['course' => $tcd->id]);

        // Course matching termcode regex, but not matching a template.
        // Now there IS a default template, so this should use it.
        $this->assertEquals(1, $DB->count_records('url'));
        $cd = $this->getDataGenerator()->create_course(
            [
                'idnumber' => 'XLSB7201640',
            ]
        );
        $this->assertEquals(2, $DB->count_records('url'));
        $this->assertEquals(2, $DB->count_records('assign'));

        // Check logging.
        $logs = $DB->get_records('logstore_standard_log', ['component' => 'local_course_template']);
        $this->assertEquals(3, count($logs));
        $log = end($logs);
        $other = @unserialize($log->other) ? unserialize($log->other) : json_decode($log->other, true);
        $this->assertEquals($cd->id, $other['courseid']);
        $this->assertEquals($tcd->id, $other['templateid']);

        // Course with no template.
        $this->getDataGenerator()->create_course();

        $this->assertEquals(2, $DB->count_records('url'));
        $this->assertEquals(2, $DB->count_records('label'));
        $this->assertEquals(2, $DB->count_records('assign'));

        // Check logging.
        $logs = $DB->get_records('logstore_standard_log', ['component' => 'local_course_template']);
        $this->assertEquals(3, count($logs));

        // Bulk course creation.
        $category1 = $this->getDataGenerator()->create_category();
        for ($categoryid = 2; $categoryid <= 20; $categoryid++) {
            $category = $this->getDataGenerator()->create_category(['parent' => $category1->id]);
            for ($course = 1; $course <= 10; $course++) {
                $coursenum = ($categoryid * 10) + $course;
                $this->getDataGenerator()->create_course([
                    'category' => $category->id, 'idnumber' => str_pad($coursenum, 5, '0', STR_PAD_LEFT). '.201610']);
            }
        }
        $this->assertEquals(192, $DB->count_records('label'));
        $this->assertEquals(2, $DB->count_records('assign'));

        // Check logging.
        $logs = $DB->get_records('logstore_standard_log', ['component' => 'local_course_template']);
        $this->assertEquals(193, count($logs));

        // Enable start and end date copying.
        set_config('copydates', 1, 'local_course_template');

        // Course matching 201610 template.
        $c4 = $this->getDataGenerator()->create_course(
            [
                'idnumber' => 'XLSB7201610',
            ]
        );
        $this->assertEquals(193, $DB->count_records('label'));
        $this->assertEquals(2, $DB->count_records('assign'));

        // Reload the course.
        $c4 = get_course($c4->id);
        $this->assertEquals('2023-08-28', date("Y-m-d", $c4->startdate));
        $this->assertEquals('2023-12-11', date("Y-m-d", $c4->enddate));

        // Check logging.
        $logs = $DB->get_records('logstore_standard_log', ['component' => 'local_course_template']);
        $this->assertEquals(194, count($logs));
        $log = end($logs);
        $other = @unserialize($log->other) ? unserialize($log->other) : json_decode($log->other, true);
        $this->assertEquals($c4->id, $other['courseid']);
        $this->assertEquals($tc1->id, $other['templateid']);
    }

    public function test_course_caching(): void {
        global $DB;

        $this->resetAfterTest(true);

        // Enable logging.
        $this->preventResetByRollback();

        // Configure the plugin.
        set_config('extracttermcode', '/[A-Za-z0-9\.]+([0-9]{6})/', 'local_course_template');
        set_config('templatenameformat', 'Template-[TERMCODE]', 'local_course_template');
        set_config('defaulttemplate', 'default-template', 'local_course_template');
        set_config('enablecaching', 1, 'local_course_template');

        // Create the template course.
        $tc1 = $this->getDataGenerator()->create_course(
            [
                'name' => 'Template Course 1',
                'shortname' => 'Template-201610',
            ]
        );
        $label1 = $this->getDataGenerator()->create_module('label',
            ['course' => $tc1->id]);

        // Course matching 201610 template.
        $c1 = $this->getDataGenerator()->create_course(
            [
                'idnumber' => '1000.201610',
            ]
        );

        // Verify that the id was cached when $c1 was created.
        $courseid = helper::get_cached_course_id($tc1->shortname);
        $this->assertEquals($tc1->id, $courseid);
        $coursebackup = backup::get_cached_course($tc1->id);
        $this->assertInstanceOf('stored_file', $coursebackup);
        $this->assertEquals(2, $DB->count_records('label'));

        $label2 = $this->getDataGenerator()->create_module('label',
            ['course' => $tc1->id]);

        // Course matching 201610 template.
        $c2 = $this->getDataGenerator()->create_course(
            [
                'idnumber' => '1001.201610',
            ]
        );

        // Verify that only two new labels have been created; the cached backup
        // only has one label.
        $this->assertEquals(4, $DB->count_records('label'));

        // Disable caching.
        set_config('enablecaching', 0, 'local_course_template');
        $courseid = helper::get_cached_course_id($tc1->shortname);
        $this->assertEquals(false, $courseid);
        $coursebackup = backup::get_cached_course($tc1->id);
        $this->assertEquals(false, $coursebackup);

        // Course matching 201610 template.
        $c3 = $this->getDataGenerator()->create_course(
            [
                'idnumber' => '1002.201610',
            ]
        );

        // Verify that the new course creation has two labels.
        $this->assertEquals(6, $DB->count_records('label'));
    }
}
