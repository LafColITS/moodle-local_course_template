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
 * Local course template library functions.
 *
 * @package   local_course_template
 * @copyright 2020 Lafayette College ITS
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_course_template;

defined('MOODLE_INTERNAL') || die();

/**
 * Optionally purge the cache.
 *
 * Optionally purge the cache if the user disables caching.
 */
function local_course_template_update_cache() {
    $enablecaching = get_config('local_course_template', 'enablecaching');
    if (empty($enablecaching) || $enablecaching == 0) {
        cache::clear();
    }
}
