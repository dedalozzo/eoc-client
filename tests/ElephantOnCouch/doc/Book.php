<?php

//! @file Book.php
//! @brief This file contains the Book class.
//! @details
//! @author Filippo F. Fadda


require_once(__DIR__ . "/Item.php");


class Book extends Item {

  public function setBody($body) {
    $this->meta["body"] = $body;
  }


  public function getBody() {
    return $this->meta["body"];
  }


  public function setPositive($positive) {
    $this->meta["positive"] = $positive;
  }


  public function getPositive() {
    return $this->meta["positive"];
  }


  public function setNegative($negative) {
    $this->meta["negative"] = $negative;
  }


  public function getNegative() {
    return $this->meta["negative"];
  }

}
