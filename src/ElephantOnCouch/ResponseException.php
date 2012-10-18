<?php

//! @file ResponseException.php
//! @brief This file contains the ResponseException class.
//! @details
//! @author Filippo F. Fadda


namespace FFF\ElephantOnCouch;


//! @brief TODO
class ResponseException extends \Exception {
  protected $error;
  protected $reason;

  public function __construct($message, $code, $error = "", $reason = "") {
    parent::__construct($message, $code);

    $this->error = $error;
    $this->reason = $reason;
  }

  final public function getError() {
    return $this->error;
  }

  final public function getReason() {
    return $this->reason;
  }

}
