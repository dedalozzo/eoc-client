<?php

/**
 * @file ServerErrorException.php
 * @brief This file contains the ServerErrorException class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Exception;


/**
 * @brief Exception thrown when a server error is encountered (5xx codes)
 */
class ServerErrorException extends BadResponseException {}