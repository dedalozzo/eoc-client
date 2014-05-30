<?php

/**
 * @file IChunkHook.php
 * @brief This file contains the IChunkHook interface.
 * @details
 * @author Filippo F. Fadda
 */


//! This is the hooks namespace.
namespace ElephantOnCouch\Hook;


/**
 * @brief You might implement this interface to deal with chunked responses.
 * @details You can write a class that implements the IChunkHook interface, when you need to deal with a chunked
 * responses. Some Couch methods, like queryView(), use the interface - when provided - to call process() every time
 * a response chunk is read. You can implement your own handler, to display a partial result using AJAX for example.
 */
interface IChunkHook {

  /**
   * @brief Processes the response chunk.
   * @param[in] string $chunk A chunk.
   */
  public function process($chunk);

}