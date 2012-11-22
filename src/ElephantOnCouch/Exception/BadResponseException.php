<?php

//! @file BadResponseException.php
//! @brief This file contains the BadResponseException class.
//! @details
//! @author Filippo F. Fadda


//! @brief The CouchDB's errors namespace.
namespace ElephantOnCouch\Exception;


use Rest\Exception\BadResponseException as RestBadResponseException;


//! @brief Exception thrown when a bad Response is received.
class BadResponseException extends RestBadResponseException {

  public function __toString() {
    $error = $this->response->getBodyAsArray();

    $this->info[] = "[CouchDB Error Code] ".$error["error"];
    $this->info[] = "[CouchDB Error Reason] ".$error["reason"];

    return parent::__toString();
  }

}