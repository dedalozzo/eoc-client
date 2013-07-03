<?php

//! @file ServerInfo.phpphp
//! @brief This file contains the ServerInfo class.
//! @details
//! @author Filippo F. Fadda


//! @brief The CouchDB's information namespace.
namespace ElephantOnCouch\Info;


use ElephantOnCouch\Helper\Properties;


//! @brief This class contains the CouchDB's version and MOTD. It's used by Couch.getSvrInfo() method.
//! @details Since this class uses the <i>Properties</i> trait, you don't need to call the getter methods to obtain information
//! about server.
//! @nosubgrouping
class ServerInfo {
  use Properties;

  //! @name Properties
  //@{

  //! @brief CouchDB MOTD (Message Of The Day).
  private $motd;

  //! @brief CouchDB server version.
  private $version;

  //@}


  //! @brief Creates the object.
  public function __construct($motd, $version) {
    $this->motd = $motd;
    $this->version = "CouchDB ".$version;
  }


  public function getMotd() {
    return $this->motd;
  }


  public function getVersion() {
    return $this->version;
  }


  //! @brief Overrides the magic method to convert the object to a string.
  public function __toString() {
    return $this->motd.PHP_EOL.$this->version.PHP_EOL;
  }

}