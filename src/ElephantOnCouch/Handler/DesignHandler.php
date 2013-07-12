<?php

//! @file DesignHandler.php
//! @brief This file contains the DesignHandler class.
//! @details
//! @author Filippo F. Fadda


//! @brief The CouchDB's design document handlers namespace.
namespace ElephantOnCouch\Handler;


use ElephantOnCouch\Helper\Properties;


//! @brief This class defines the interface for all the concrete CouchDB's handlers.
//! @details To create a new handler you must inherit from this class. This is the only extension point for handlers.
//! In case of CouchDB design documents' structure changes, you just need to create a new handler, starting from here.
//! @nosubgrouping
abstract class DesignHandler implements HandlerInterface {
  use Properties; // This is a trait, not a namespace or a class.
}