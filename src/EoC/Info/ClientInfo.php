<?php

/**
 * @file ClientInfo.php
 * @brief This file contains the ClientInfo class.
 * @details
 * @author Filippo F. Fadda
 */


namespace EoC\Info;


use EoC\Couch;
use EoC\Version;

use ToolBag\Extension;


/**
 * @brief This is an information only purpose class. It's used by Couch::getClientInfo() method.
 * @details Since this class uses the `TProperty` trait, you don't need to call the getter methods to obtain
 * information about the client.
 * @nosubgrouping
 *
 * @cond HIDDEN_SYMBOLS
 *
 * @property string $version
 *
 * @endcond
 */
class ClientInfo {
  use Extension\TProperty;

  /** @name Properties */
  //!@{

  //! @brief Client version.
  private $version;

  //!@}


  /**
   * @brief Creates the object.
   */
  public function __construct() {
    $this->version = Couch::USER_AGENT_NAME." ".Version::getNumber();
  }


  /**
   * @brief Overrides the magic method to convert the object to a string.
   */
  public function __toString() {
    return $this->version.PHP_EOL;
  }


  //! @cond HIDDEN_SYMBOLS

  public function getVersion() {
    return $this->version;
  }

  //! @endcond

}