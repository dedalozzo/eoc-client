<?php

//! @file SvrInfo.class.php
//! @brief This file contains the SvrInfo class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief This class contains the CouchDB's version and MOTD. It's used by getSvrInfo() method.
class SvrInfo {
  use Properties;

  private $motd; //!< CouchDB MOTD (Message Of The Day).
  private $serverVersion; //!< CouchDB server version.

  public function __construct($motd, $serverVersion) {
    $this->motd = $motd;
    $this->serverVersion = "CouchDB ".$serverVersion;
  }

  public function getMotd() {
    return $this->motd;
  }

  public function getServerVersion() {
    return $this->serverVersion;
  }

}

?>