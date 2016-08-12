<?php

// preferences.php - user prefs for calendar

require_once('../config.php');

$courseid = required_param('course', PARAM_INT);
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
debugging('This page should no longer be used, use the calendar preferences page instead');

$redirect = new moodle_url("/user/calendar.php", array('id' => $USER->id, 'course' => $courseid));
redirect($redirect);
