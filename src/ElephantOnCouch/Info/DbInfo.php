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
class DbInfo {
  use Helper\Properties;

  private $name;
  private $diskSize;
  private $dataSize;
  private $diskFormatVersion;
  private $instanceStartTime;
  private $docCount;
  private $docDelCount;
  private $updateSeq;
  private $purgeSeq;
  private $compactRunning;
  private $committedUpdateSeq;


  //! @brief This constructor is used by src.getDbinfo() method.
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


  //! @brief Returns the database name.
  public function getName() {
    return $this->name;
  }


  //! @brief Gets the current size in Bytes of the database. Note: size of views indexes on disk are not included.
  public function getDiskSize() {
    return $this->diskSize;
  }


  //! @brief Returns the current size in Bytes of the database documents. Deleted documents or revision are not counted.
  public function getDataSize() {
    return $this->dataSize;
  }


  //! @brief Gets the current version of the internal database format on disk.
  public function getDiskFormatVersion() {
    return $this->diskFormatVersion;
  }


  //! @brief Returns the timestamp of CouchDBs start time.
  public function getInstanceStartTime() {
    return $this->instanceStartTime;
  }


  //! #brief Returns the number of documents (including design documents) in the database.
  public function getDocCount() {
    return $this->docCount;
  }


  //! @brief Returns the number of deleted documents (including design documents) in the database.
  public function getDocDelCount() {
    return $this->docDelCount;
  }


  //! @brief Returns the current number of updates to the database.
  public function getUpdateSeq() {
    return $this->updateSeq;
  }


  //! @brief Returns the number of purge operations.
  public function getPurgeSeq() {
    return $this->purgeSeq;
  }


  //! @brief Indicates if a compaction is running.
  public function getCompactRunning() {
    return $this->compactRunning;
  }


  //! @brief Returns of committed updates number.
  public function getCommittedUpdateSequence() {
    return $this->committedUpdateSeq;
  }

}