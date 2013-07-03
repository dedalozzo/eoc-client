<?php

//! @file DbInfo.php
//! @brief This file contains the DbInfo class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Info;


use ElephantOnCouch\Helper;


//! @brief This is an information only purpose class. It's used by Couch.getDbInfo() method.
//! @details Since this class uses the <i>Properties</i> trait, you don't need to call the getter methods to obtain information
//! about database.
//! @nosubgrouping
class DbInfo {
  use Helper\Properties;

  //! @name Properties
  //@{

  //! @brief Returns the database name.
  private $name;

  //! @brief Gets the current size in Bytes of the database. Note: size of views indexes on disk are not included.
  private $diskSize;

  //! @brief Returns the current size in Bytes of the database documents. Deleted documents or revision are not counted.
  private $dataSize;

  //! @brief Gets the current version of the internal database format on disk.
  private $diskFormatVersion;

  //! @brief Returns the timestamp of CouchDBs start time.
  private $instanceStartTime;

  //! #brief Returns the number of documents (including design documents) in the database.
  private $docCount;

  //! @brief Returns the number of deleted documents (including design documents) in the database.
  private $docDelCount;

  //! @brief Returns the current number of updates to the database.
  private $updateSeq;

  //! @brief Returns the number of purge operations.
  private $purgeSeq;

  //! @brief Indicates if a compaction is running.
  private $compactRunning;

  //! @brief Returns of committed updates number.
  private $committedUpdateSeq;

  //@}


  //! @brief Creates an instance based on the provided JSON array.
  public function __construct(array $info) {
    if (Helper\ArrayHelper::isAssociative($info)) {
      $this->name = $info['db_name'];
      $this->diskSize = $info['disk_size'];
      $this->dataSize = $info['data_size'];
      $this->diskFormatVersion = $info['disk_format_version'];
      $this->instanceStartTime = $info['instance_start_time'];
      $this->docCount = $info['doc_count'];
      $this->docDelCount = $info['doc_del_count'];
      $this->updateSeq = $info['update_seq'];
      $this->purgeSeq = $info['purge_seq'];

      if (is_null($info['compact_running']))
        $this->compactRunning = FALSE;
      else
        $this->compactRunning = TRUE;

      $this->committedUpdateSeq = $info['committed_update_seq'];
    }
    else
      throw new \Exception("\$info must be an associative array.");
  }


  // Returns an array with the uptime in days, hours, minutes and seconds.
  private function uptime() {
    $microsecondsInASecond = 1000000;
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;

    // Gets the current timestamp in microseconds.
    $timestamp = microtime(TRUE);

    // Subtracts from the current timestamp the last timestamp server was started.
    $microseconds = $timestamp - (float)$this->instanceStartTime;

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

    $uptime = '%d days, %d hours, %d minutes, %d seconds';

    return sprintf($uptime, $days, $hours, $minutes, $seconds);
  }


  public function getName() {
    return $this->name;
  }


  public function getDiskSize() {
    return $this->diskSize;
  }


  public function getDataSize() {
    return $this->dataSize;
  }


  public function getDiskFormatVersion() {
    return $this->diskFormatVersion;
  }


  public function getInstanceStartTime() {
    return $this->instanceStartTime;
  }


  public function getDocCount() {
    return $this->docCount;
  }


  public function getDocDelCount() {
    return $this->docDelCount;
  }


  public function getUpdateSeq() {
    return $this->updateSeq;
  }


  public function getPurgeSeq() {
    return $this->purgeSeq;
  }


  public function getCompactRunning() {
    return $this->compactRunning;
  }


  public function getCommittedUpdateSequence() {
    return $this->committedUpdateSeq;
  }


  //! @brief Overrides the magic method to convert the object to a string.
  public function __toString() {
    $buffer = "";
    $buffer .= "[CouchDB Uptime] ".$this->uptime().PHP_EOL
    $buffer .= PHP_EOL;

    $buffer .= "[Database Name] ".$this->name.PHP_EOL;
    $buffer .= "[Database Disk Size (Bytes)] ".$this->diskSize.PHP_EOL;
    $buffer .= "[Database Data Size (Bytes)] ".$this->dataSize.PHP_EOL;
    $buffer .= "[Database Disk Format Version] ".$this->diskFormatVersion.PHP_EOL;
    $buffer .= PHP_EOL;

    $compactRunning = ($this->compactRunning) ? 'active' : 'inactive';
    $buffer .= "[Database Compaction] ".$compactRunning.PHP_EOL;
    $buffer .= PHP_EOL;

    $buffer .= "[Database Number of Documents] ".$this->docCount.PHP_EOL;
    $buffer .= "[Database Number of Deleted Documents] ".$this->docDelCount.PHP_EOL;
    $buffer .= "[Database Number of Updates] = ".$this->updateSeq.PHP_EOL;
    $buffer .= "[Database Number of Purge Operations] ".$this->purgeSeq.PHP_EOL;
    $buffer .= "[Database Number of Committed Updates] ".$this->committedUpdateSeq.PHP_EOL;

    return $buffer;
  }

}