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
 * Date condition.
 *
 * @package availability_week
 * @copyright 2014 Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_week;

defined('MOODLE_INTERNAL') || die();

/**
 * Week from course start condition.
 *
 * @package availability_week
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {

    /** @var int Time (Unix epoch seconds) for condition. */
    private $weekfromstart;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {

        // Get week.
        if (isset($structure->w)) {
            $this->weekfromstart = $structure->w;
        } else {
            throw new \coding_exception('Missing or invalid ->w for week condition');
        }
    }

    public function save() {
        return (object)array('type' => 'week',
                'w' => $this->weekfromstart);
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        return $this->is_available_for_all($not);
    }

    public function is_available_for_all($not = false) {
        global $COURSE;

        // Check condition.
        $now = self::get_time();
        $allow = $now >= ($this->weekfromstart * DAYSECS * 7) + $COURSE->startdate;

        if ($not) {
            $allow = !$allow;
        }

        return $allow;
    }

    public function get_description($full, $not, \core_availability\info $info) {
        return $this->get_either_description($not, false);
    }

    public function get_standalone_description(
            $full, $not, \core_availability\info $info) {
        return $this->get_either_description($not, true);
    }

    /**
     * Shows the description using the different lang strings for the standalone
     * version or the full one.
     *
     * @param bool $not True if NOT is in force
     * @param bool $standalone True to use standalone lang strings
     */
    protected function get_either_description($not, $standalone) {

        $satag = $standalone ? 'short_' : 'full_';
        return get_string($satag . 'week', 'availability_week',
                self::show_week($this->weekfromstart));
    }

    protected function get_debug_string() {
        return $this->weekfromstart;
    }

    /**
     * Gets time. This function is implemented here rather than calling time()
     * so that it can be overridden in unit tests. (Would really be nice if
     * Moodle had a generic way of doing that, but it doesn't.)
     *
     * @return int Current time (seconds since epoch)
     */
    protected static function get_time() {
        return time();
    }

    /**
     * Shows a time either as a date or a full date and time, according to
     * user's timezone.
     *
     * @param int $week the relative week shift from course start
     * @param bool $dateonly If true, uses date only
     * @param bool $until If true, and if using date only, shows previous date
     * @return string Date
     */
    protected function show_week($week, $dateonly = false) {
        global $COURSE;

        $time = $COURSE->startdate + $this->weekfromstart * WEEKSECS;
        return '+'.$this->weekfromstart.' ('.userdate($time, get_string($dateonly ? 'strftimedate' : 'strftimedatetime', 'langconfig')).')';
    }

    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        return true;
    }
}
