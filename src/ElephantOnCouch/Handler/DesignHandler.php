<?php

//! @file DesignHandler.php
//! @brief This file contains the DesignHandler class.
//! @details
//! @author Filippo F. Fadda


//! @brief The CouchDB's design document handlers namespace.
namespace ElephantOnCouch\Handler;


use ElephantOnCouch\Helper\Properties;
use ElephantOnCouch\Doc\DesignDoc;


//! @brief This class defines the interface for all the concrete CouchDB's handlers.
//! @details To create a new handler you must inherit from this class. This is the only extension point for handlers.
//! In case of CouchDB design documents' structure changes, you just need to create a new handler, starting from here.
//! @nosubgrouping
abstract class DesignHandler {
  use Properties; // This is a trait, not a namespace or a class.

  protected $doc;


  //! @brief Creates an handler instance.
  //! @param[in] DesignDoc $doc A design document.
  public function __construct(DesignDoc $doc) {
    $this->doc = $doc;
  }


  //! @brief Returns the handler's section.
  //! @details Every CouchDB's handler is stored in a particular design document section. Every class that extends the
  //! abstract handler DesignHandler, must implement this method to return his own section.
  //! @return string
  abstract public function getSection();


  //! @brief You must always check the handler's consistence before every call to <i>getAttributes</i> method.
  //! @return boolean
  abstract public function isConsistent();


  //! @brief Returns the handler's attributes.
  //! @return string|array
  abstract public function asArray();

}