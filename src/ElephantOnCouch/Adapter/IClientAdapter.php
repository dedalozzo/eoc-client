<?php

//! @file IClientAdapter.php
//! @brief This file contains the IClientAdapter class.
//! @details
//! @author Filippo F. Fadda


//! The HTTP clients namespace.
namespace ElephantOnCouch\Adapter;


use ElephantOnCouch\Message\Request;
use ElephantOnCouch\Hook;


/**
 * @brief An HTTP client adapter interface
 * @details To create your own adapter you must implement this interface (or easily inherit from AbstractAdapter).
 */
interface IClientAdapter {


  /**
   * @brief This method is used to send an HTTP Request.
   * @details The method takes two parameter: the first one `$request` is mandatory; the second one is optional.\n
   * @param[in] Request $request The Request object.
   * @param[in] IChunkHook $chunkHook (optional) A class instance that implements the IChunkHook interface.
   * @return Response
   * @attention The method must return an instance of a Response class.
   */
  function send(Request $request, Hook\IChunkHook $chunkHook = NULL);

} 