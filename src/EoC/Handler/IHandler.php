<?php

/**
 * @file IHandler.php
 * @brief This file contains the IHandler interface.
 * @details
 * @author Filippo F. Fadda
 * */


namespace EoC\Handler;


/**
 * @brief All the concrete Design Handlers must implement this interface.
 */
interface IHandler {


  /**
   * @brief Returns the handler's section.
   * @details Every CouchDB's handler is stored in a particular design document section. Every class that extends the
   * abstract handler DesignIHandler, must implement this method to return his own section.
   * @return string
   */
  static function getSection();


  /**
   * @brief Returns `true` if the handler is consistent, `false` otherwise.
   * @attention You must always check the handler's consistence before every call to asArray() method.
   * @return bool
   */
  function isConsistent();


  /**
   * @brief Returns the handler's attributes.
   * @return string|array
   */
  function asArray();

}