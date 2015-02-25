<?php

/**
 * @file DesignIHandler.php
 * @brief This file contains the DesignIHandler class.
 * @details
 * @author Filippo F. Fadda
 */


//! The CouchDB's design document handlers namespace.
namespace EoC\Handler;


use EoC\Extension;


/**
 * @brief This class defines the interface for all the concrete CouchDB's handlers.
 * @details To create a new handler you must inherit from this class. This is the only extension point for handlers.
 * In case of CouchDB design documents' structure changes, you just need to create a new handler, starting from here.
 * @nosubgrouping
 */
abstract class DesignHandler implements IHandler {
  use Extension\TProperty; // This is a trait, not a namespace or a class.
}