<?php

//! @file SvrInfo.php
//! @brief This file contains the SvrInfo class.
//! @details
//! @author Filippo F. Fadda


//! @brief The CouchDB's information namespace.
namespace ElephantOnCouch\Info;


use ElephantOnCouch\Helper\Properties;


//! @brief This class contains the CouchDB's version and MOTD. It's used by Couch.getSvrInfo() method.
//! @details Since this class uses the <i>Properties</i> trait, you don't need to call the getter methods to obtain information
//! about server.
//! @nosubgrouping
class SvrInfo {
  use Properties;

  //! @name Properties
  //@{

  //! @brief CouchDB MOTD (Message Of The Day).
  private $motd;

  //! @brief CouchDB server version.
  private $serverVersion;

  //@}


  //! @brief Creates the object.
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


  //! @brief Overrides the magic method to convert the object to a string.
  public function __toString() {
    return $this->motd.PHP_EOL.$this->serverVersion.PHP_EOL;
  }

}