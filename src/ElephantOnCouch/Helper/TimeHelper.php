<?php

//! @file TimeHelper.php
//! @brief This file contains the TimeHelper class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Helper;


//! @brief Helper with common methods to manipulate timestamps.
//! @nosubgrouping
class TimeHelper {

  //! @brief Returns a string with the elapsed time, from the provided timestamp, in days, hours, minutes and seconds.
  //! @param[in] string $timestamp A timestamp in microseconds.
  //! @return string
  public static function since($timestamp) {
    $microsecondsInASecond = 1000000;
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;

    // Gets the current timestamp in microseconds.
    $now = microtime(TRUE);

    // Subtracts from the current timestamp the last timestamp server was started.
    $microseconds = ($now * $microsecondsInASecond) - (float)$timestamp;

    // Converts microseconds in seconds.
    $seconds = floor($microseconds / $microsecondsInASecond);

    // Extracts days.
    $days = (int)floor($seconds / $secondsInADay);

    // Extracts hours.
    $hourSeconds = $seconds % $secondsInADay;
    $hours = (int)floor($hourSeconds / $secondsInAnHour);

    // Extracts minutes.
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = (int)floor($minuteSeconds / $secondsInAMinute);

    // Extracts the remaining seconds.
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = (int)ceil($remainingSeconds);

    $since = '%d days, %d hours, %d minutes, %d seconds';

    return sprintf($since, $days, $hours, $minutes, $seconds);
  }

}