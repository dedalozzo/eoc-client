<?php

//! @file HandlerInterface.php
//! @brief This file contains the HandlerInterface interface.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Handler;


//! @brief All the concrete Design Handlers must implement this interface.
interface HandlerInterface {


  //! @brief Returns the handler's section.
  //! @details Every CouchDB's handler is stored in a particular design document section. Every class that extends the
  //! abstract handler DesignHandler, must implement this method to return his own section.
  //! @return string
  static function getSection();


  //! @brief You must always check the handler's consistence before every call to <i>getAttributes</i> method.
  //! @return boolean
  function isConsistent();


  //! @brief Returns the handler's attributes.
  //! @return string|array
  function asArray();

}