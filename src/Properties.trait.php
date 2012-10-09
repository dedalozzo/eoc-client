<?php

//! @file Properties.trait.php
//! @brief This file contains the Doc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief TODO
trait Properties {

  public function __get($name) {
    if (method_exists($this, ($method = 'get'.ucfirst($name))))
      return $this->$method();
    else
      throw new \BadMethodCallException("Method $method is not implemented for property $name.");
  }

  public function __isset($name) {
    if (method_exists($this, ($method = 'isset'.ucfirst($name))))
      return $this->$method();
    else
      throw new \BadMethodCallException("Method $method is not implemented for property $name.");
  }

  public function __set($name, $value) {
    if (method_exists($this, ($method = 'set'.ucfirst($name))))
      $this->$method($value);
    else
      throw new \BadMethodCallException("Method $method is not implemented for property $name.");
  }

  public function __unset($name) {
    if (method_exists($this, ($method = 'unset'.ucfirst($name))))
      $this->$method();
    else
      throw new \BadMethodCallException("Method $method is not implemented for property $name.");
  }
}

?>