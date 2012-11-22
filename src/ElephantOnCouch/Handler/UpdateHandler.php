<?php

//! @file UpdateHandler.php
//! @brief This file contains the UpdateHandler class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Handler;


//! @brief TODO
final class UpdateHandler extends DesignHandler {
  const UPDATES = "updates";

  private $name;


  //! @brief Creates a UpdateHandler class instance.
  //! @param[in] string $name Handler name.
  public function __construct($name) {
    $this->setName($name);
  }


  public function getName() {
    return $this->name;
  }


  public function setName($value) {
    $this->name = (string)$value;
  }


  public static function getSection() {
    return self::UPDATES;
  }


  public function isConsistent() {
    // TODO: Implement isConsistent() method.
  }


  public function asArray() {
    // TODO: Implement getAttributes() method.
  }

}
