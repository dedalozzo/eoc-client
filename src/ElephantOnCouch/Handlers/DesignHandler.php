<?php

//! @file DesignHandler.php
//! @brief This file contains the DesignHandler class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Handlers;


//! @brief This class defines the interface for all the concrete CouchDB's handlers.
//! @details To create a new handler you must inherit from this class. This is the only extension point for handlers.
//! In case of CouchDB design documents' structure changes, you just need to create a new handler, starting from here.
//! @nosubgrouping
abstract class DesignHandler {
  use \ElephantOnCouch\Properties; // This is a trait, not a namespace or a class.


  //! @brief Returns the handler's section.
  //! @return string
  abstract public static function getSection();


  //! @brief You must always check the handler's consistence before every call to <i>getAttributes</i> method.
  //! @return boolean
  abstract public function isConsistent();


  //! @brief Returns the handler's attributes.
  //! @return string|array
  abstract public function asArray();
}

?>