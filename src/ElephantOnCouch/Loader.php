<?php

//! @file Loader.class.php
//! @brief This file contains the Loeader class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief TODO
class Loader {

  static public function init() {
    spl_autoload_extensions(".class.php,.trait.php");
    spl_autoload_register();
  }

}

?>
