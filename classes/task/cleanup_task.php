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

namespace local_course_template\task;

class cleanup_task extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('cleanuptask', 'local_course_template');
    }

    public function execute() {
        global $DB;

        // Find and prune template backups.
        $cache = cache::make('local_course_template', 'backups');
        $backups = $DB->get_records('files', array('component' => 'local_course_template', 'filearea' => 'backup'));
        $fs = get_file_storage();
        foreach ($backups as $record) {
            $file = $fs->get_file(
                $record->contextid,
                $record->component,
                $record->filearea,
                $record->itemid,
                $record->filepath,
                $record->filename
            );
            if ($file) {
                $file->delete();
                $cache->delete($record->contextid);
            }
        }
    }
}
