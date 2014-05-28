<?php

//! @file ClientInfo.php
//! @brief This file contains the ClientInfo class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Info;


use ElephantOnCouch\Extension\TProperty;
use ElephantOnCouch\Couch;


//! @brief This is an information only purpose class. It's used by Couch.getClientInfo() method.
//! @details Since this class uses the `TProperty` trait, you don't need to call the getter methods to obtain
//! information about the client.
//! @nosubgrouping
class ClientInfo {
  use TProperty;

  //! @name TProperty
  //@{

  //! @brief Client version.
  private $version;

  //! @brief Selected transport method.
  private $transportMethod;

  //! @brief Protocol version.
  private $protocolVersion;

  //@}


  //! @brief Creates the object.
  public function __construct() {
    $this->version = Couch::USER_AGENT_NAME." ".Couch::USER_AGENT_VERSION;

    if (Couch::getTransportMethod() == Couch::SOCKET_TRANSPORT)
      $this->transportMethod = 'PHP Sockets';
    else
      $this->transportMethod = 'cURL';

    $this->protocolVersion = Couch::HTTP_VERSION;
  }


  public function getVersion() {
    return $this->version;
  }


  public function getTransportMethod() {
    return $this->transportMethod;
  }


  public function getProtocolVersion() {
    return $this->protocolVersion;
  }


  //! @brief Overrides the magic method to convert the object to a string.
  public function __toString() {
    $buffer = $this->version.PHP_EOL;
    $buffer .= "Transport Method: ".$this->transportMethod.PHP_EOL;
    $buffer .= "Protocol Version: ".$this->protocolVersion.PHP_EOL;

    return $buffer;
  }

}