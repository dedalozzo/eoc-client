<?php

//! @file ShowHandler.php
//! @brief This file contains the ShowHandler class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Handlers;


//! @brief TODO
final class ShowHandler extends DesignHandler {
  const SHOWS = "shows";

  private $name;


  //! @brief Creates a ShowHandler class instance.
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
    return self::SHOWS;
  }


  public function isConsistent() {
    // TODO: Implement isConsistent() method.
  }


  public function getAttributes() {
    // TODO: Implement getAttributes() method.
  }

}

?>