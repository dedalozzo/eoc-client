<?php

//! @file Properties.php
//! @brief This file contains the Properties trait.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief This trait can be used by a class to emulate the C# properties.
//! @see http://www.programmazione.it/index.php?entity=eitem&idItem=48399
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