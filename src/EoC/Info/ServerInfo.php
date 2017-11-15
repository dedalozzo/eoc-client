<?php

/**
 * @file ServerInfo.php
 * @brief This file contains the ServerInfo class.
 * @details
 * @author Filippo F. Fadda
 */


//! The CouchDB's information namespace.
namespace EoC\Info;


use Meta\Extension;


/**
 * @brief This class contains the CouchDB's version and MOTD. It's used by Couch::getSvrInfo() method.
 * @details Since this class uses the `TProperty` trait, you don't need to call the getter methods to obtain information
 * about server.
 * @nosubgrouping
 *
 * @cond HIDDEN_SYMBOLS
 *
 * @property string $motd
 * @property string $version
 *
 * @endcond
 */
class ServerInfo {
  use Extension\TProperty;

  /** @name Properties */
  //!@{

  //! CouchDB MOTD (Message Of The Day).
  private $motd;

  //! CouchDB server version.
  private $version;

  //!@}


  /**
   * @brief Creates the object.
   */
  public function __construct($motd, $version) {
    $this->motd = $motd;
    $this->version = "CouchDB ".$version;
  }


  /**
   * @brief Overrides the magic method to convert the object to a string.
   */
  public function __toString() {
    return $this->motd.PHP_EOL.$this->version.PHP_EOL;
  }


  //! @cond HIDDEN_SYMBOLS

  public function getMotd() {
    return $this->motd;
  }


  public function getVersion() {
    return $this->version;
  }

  //! @endcond

}