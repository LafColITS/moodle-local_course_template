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
 * Language strings for the plugin.
 *
 * @package    local_course_template
 * @copyright  2016 Lafayette College ITS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['cachedef_backups'] = 'Course template backups';
$string['cachedef_templates'] = 'Course template course ids';
$string['cleanuptask'] = 'Cleanup course template backups';
$string['defaulttemplate'] = 'Default template course shortname';
$string['defaulttemplate_desc'] = 'Shortname of the default course template. Courses which do not match a template will use this one, if it exists.';
$string['enablecaching'] = 'Enable caching';
$string['enablecaching_desc'] = 'When caching is enabled the plugin will create a backup of the template course and then re-use the backup for new courses until the cache is cleared or the backup is deleted.';
$string['event:template_copied:description'] = 'The template course with id {$a->templateid} was copied into the course with id {$a->courseid}';
$string['event:template_copied:name'] = 'Course template copied';
$string['extracttermcode'] = 'Term code';
$string['extracttermcode_desc'] = 'Used to populate [TERMCODE]. Derived from course idnumber.';
$string['pluginname'] = 'Use template on course creation';
$string['privacy:metadata'] = 'The Use template on course creation plugin does not store any personal data.';
$string['templatenameformat'] = 'Template shortname format';
$string['templatenameformat_desc'] = 'Expected shortname format for template courses';
