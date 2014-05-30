<?php

//! @file RewriteHandler.php
//! @brief This file contains the RewriteHandler class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Handler;


/**
 * @brief
 * @todo To be documented and implemented.
 */
final class RewriteHandler extends DesignHandler {
  const REWRITES = "rewrites";

  private $name;


  /**
   * @brief Creates a RewriteHandler class instance.
   * @param[in] string $name Handler name.
   */
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
    return self::REWRITES;
  }


  public function isConsistent() {
    // todo Implement isConsistent() method.
  }


  public function asArray() {
    // todo Implement getAttributes() method.
  }

}
