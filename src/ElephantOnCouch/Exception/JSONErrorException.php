<?php

//! @file JSONErrorException.php
//! @brief This file contains the JSONErrorException class.
//! @details
//! @author Filippo F. Fadda


//! @brief The CouchDB's errors namespace.
namespace ElephantOnCouch\Exception;


//! @brief Exception thrown when unable to parse JSON.
class JSONErrorException extends \RuntimeException {}