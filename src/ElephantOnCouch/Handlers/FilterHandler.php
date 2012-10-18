<?php

//! @file FilterHandler.php
//! @brief This file contains the FilterHandler class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Handlers;


//! @brief TODO
final class FilterHandler extends DesignHandler {
  const FILTERS = "filters";

  private $name;


  //! @brief Creates a FilterHandler class instance.
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
    return self::FILTERS;
  }


  public function isConsistent() {
    // TODO: Implement isConsistent() method.
  }


  public function getAttributes() {
    // TODO: Implement getAttributes() method.
  }

}
