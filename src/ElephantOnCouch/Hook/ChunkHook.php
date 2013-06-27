<?php

//! @file ChunkHook.php
//! @brief This file contains the ChunkHook interface.
//! @details
//! @author Filippo F. Fadda


//! @brief This is the hooks namespace.
namespace ElephantOnCouch\Hook;


//! @brief You might implement this interface to deal with chunked responses.
//! @details You can write a class that implements the ChunkHook interface, when you need to deal with a chunked
//! responses. Some Couch methods, like queryView(), use the interface - when provided - to call process() every time
//! a response chunk is read. You can implement your own handler, to display a partial result using AJAX for example.
interface ChunkHook {

    //! @brief Processes the response chunk.
    public function process($chunk);

}