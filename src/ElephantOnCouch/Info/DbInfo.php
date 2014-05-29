<?php

//! @file DbInfo.php
//! @brief This file contains the DbInfo class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Info;


use ElephantOnCouch\Extension;


//! @brief This is an information only purpose class. It's used by Couch.getDbInfo() method.
//! @details Since this class uses the `TProperty` trait, you don't need to call the getter methods to obtain information
//! about database.
//! @nosubgrouping
class DbInfo {
  use Extension\TProperty;

  //! @name TProperty
  //@{

  //! @brief Returns the database name.
  private $name;

  //! @brief Gets the current size in Bytes of the database. Note: size of views indexes on disk are not included.
  private $diskSize;

  //! @brief Returns the current size in Bytes of the database documents. Deleted documents or revision are not counted.
  private $dataSize;

  //! @brief Gets the current version of the internal database format on disk.
  private $diskFormatVersion;

  //! @brief Returns the timestamp of the last time the database file was opened.
  //! @details This is used during the replication. When BiCouch is used this value is 0.
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
      $this->compactRunning = $info['compact_running'];
      $this->committedUpdateSeq = $info['committed_update_seq'];
    }
    else
      throw new \Exception("\$info must be an associative array.");
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
    $buffer = "Name: ".$this->name.PHP_EOL;

    if ((float)$this->instanceStartTime > 0) {
      $time = Helper\TimeHelper::since($this->instanceStartTime, TRUE);
      $since = '%d days, %d hours, %d minutes, %d seconds';
      $buffer .= "File Opened Since: ".sprintf($since, $time['days'], $time['hours'], $time['minutes'], $time['seconds']).PHP_EOL;
    }

    $buffer .= "Disk Size: ".round($this->diskSize/(1024*1024*1024), 3)." GB".PHP_EOL;
    $buffer .= "Data Size: ".round($this->dataSize/(1024*1024*1024), 3)." GB".PHP_EOL;
    $buffer .= "Disk Format Version: ".$this->diskFormatVersion.PHP_EOL;

    $compactRunning = ($this->compactRunning) ? 'active' : 'inactive';
    $buffer .= "Compaction: ".$compactRunning.PHP_EOL;

    $buffer .= "Number of Documents: ".$this->docCount.PHP_EOL;
    $buffer .= "Number of Deleted Documents: ".$this->docDelCount.PHP_EOL;
    $buffer .= "Number of Updates: ".$this->updateSeq.PHP_EOL;
    $buffer .= "Number of Purge Operations: ".$this->purgeSeq.PHP_EOL;
    $buffer .= "Number of Committed Updates: ".$this->committedUpdateSeq.PHP_EOL;

    return $buffer;
  }

}