# Use template on course creation

[![Build Status](https://travis-ci.org/LafColITS/moodle-local_course_template.svg?branch=master)](https://travis-ci.org/LafColITS/moodle-local_course_template)

This local plugin allows site administrators to create "template" courses in Moodle which will be restored into new courses on course creation. The intended use case is defining common blocks and activities for a given academic term.

## Requirements
- Moodle 3.6 (build 2018120300 or later)

## Installation
Copy the course_template folder into your /local directory and visit your Admin Notification page to complete the installation.

## Usage

The administrator will need to create a "template" course which contains the desired blocks and resources. This course will need a specially-named short name. By default the plugin will search for a course with the short name `Template-[TERMCODE]`, where `[TERMCODE]` is the matching value for `YYYYYY`. For example, if a course had the termcode `201610`, the module would search for a course with the short name `Template-201610`.

The administrator will need to define a regular expression for extracting the term code from the course idnumber. This will be used to identify which course template (if any) should be used on creation. For example, if your courses have idnumbers in the format `XXXXXX.YYYYYY`, where `YYYYYY` is the termcode, then the regular expression `/[0-9]+\.([0-9]+)/` will return `YYYYYY`.

The plugin listens on the `\core\event\course_created` event and fires immediately on course creation. Once you've given a course the necessary short name you don't need to do anything further. The plugin will create a backup of the template course and import it into the new course.

You should consider overriding Moodle's default block settings in config.php: `$CFG->defaultblocks_override = '';`. Otherwise you will get two sets of blocks on course creation. Manually configure the blocks in the template course instead.

### Default template

You may specify a default template course in the "Default template course shortname" setting. If there is a course with that shortname, it will be used as the template for any course which matches the termcode regex but does _not_ match with a specific template.

Let's say you  have termcode regex `/[A-Za-z0-9\.\-]+-([A-Z])-\d+/`, where the extracted substring is a department code. Then let's say you have the following template courses (shortnames):

- Template-BIO
- Template-HIS
- Template-MTH

These template courses will be used for Biology (BIO), History (HIS), and Math (MTH) courses respectively. Let's say you don't have specific templates for the other departments (POL, SCI, ENG, EPI, etc.), but you do want them to be based on a generalized template. You can create a fourth template course, and give it a shortname that matches the "Default template course shortname" setting value (eg `default-template`). Then, any course which matches the regex -- that is, any course from which a termcode is successfully extracted -- will be based on the default template course if it does not match a specific template.

So, the course with idnumber `intro-BIO-201910` will still use `Template-BIO`, but the course with idnumber `shakespeare-sem-ENG-201920` will use `default-template`. A course with idnumber `study-abroad-201950` will not use any template.

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
