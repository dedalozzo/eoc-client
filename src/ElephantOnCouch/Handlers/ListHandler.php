<?php

//! @file ListHandler.php
//! @brief This file contains the ListHandler class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Handlers;


//! @brief TODO
final class ListHandler extends DesignHandler {
  const LISTS = "lists";

  private $name;


  //! @brief Creates a ListHandler class instance.
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
    return self::LISTS;
  }


  public function isConsistent() {
    // TODO: Implement isConsistent() method.
  }


  public function asArray() {
    // TODO: Implement getAttributes() method.
  }

}
