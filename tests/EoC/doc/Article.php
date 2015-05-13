<?php

/**
 * @file Article.php
 * @brief This file contains the Article class.
 * @details
 * @author Filippo F. Fadda
 */


require_once(__DIR__ . "/Item.php");


class Article extends Item {

  public function setBody($body) {
    $this->meta["body"] = $body;
  }


  public function getBody() {
    return $this->meta["body"];
  }


  public function setCorrelationCode($correlationCode) {
    $this->meta["correlationCode"] = $correlationCode;
  }


  public function getCorrelationCode() {
    return $this->meta["correlationCode"];
  }

}
