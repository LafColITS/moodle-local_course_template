# Moodle Course Template

[![Build Status](https://api.travis-ci.org/LafColITS/moodle-local_course_template.png)](https://api.travis-ci.org/LafColITS/moodle-local_course_template)

This local plugin allows site administrators to create "template" courses which will be restored into new courses on course creation. The intended use case is defining common blocks and activities for a given academic term.

This is considered BETA code; please report any issues to [GitHub](https://github.com/LafColITS/moodle-local_course_template/issues).

## Requirements
- Moodle 3.1 (build 2016052300 or later)

## Installation
Copy the course_template folder into your /local directory and visit your Admin Notification page to complete the installation.

## Usage

The administrator will need to create a "template" course which contains the desired blocks and resources. This course will need a specially-named short name. By default the plugin will search for a course with the short name `Template-[TERMCODE]`, where `[TERMCODE]` is the matching value for `YYYYYY`. For example, if a course had the termcode `201610`, the module would search for a course with the short name `Template-201610`.

The administrator will need to define a regular expression for extracting the term code from the course idnumber. This will be used to identify which course template (if any) should be used on creation. For example, if your courses have idnumbers in the format `XXXXXX.YYYYYY`, where `YYYYYY` is the termcode, then the regular expression `/[0-9]+\.([0-9]+)/` will return `YYYYYY`.

The plugin listens on the `\core\event\course_created` event and fires immediately on course creation. Once you've given a course the necessary short name you don't need to do anything further. The plugin will create a backup of the template course and import it into the new course.

You should consider overriding Moodle's default block settings in config.php: `$CFG->defaultblocks_override = '';`. Otherwise you will get two sets of blocks on course creation. Manually configure the blocks in the template course instead.

### Sample regular expressions

The basic use case above, `/[0-9]+\.([0-9]+)/`, would return `YYYYYY` from the following idnumbers:

- `9999.201610`
- `3781.201730`

A more complicated example, `/[A-Za-z0-9\.]+([0-9]{6})/`, would capture the following:

- `4422.201610`
- `7866a.201730`
- `XLSB7201610`

## Acknowledgements

This plugin was inspired by the course enrollment/templating plugin in use at Wesleyan University. The restoration controller settings are derived from LSU's [Simplified Restore block](https://github.com/lsuits/simple_restore).

## Author

Charles Fulton (fultonc@lafayette.edu)
