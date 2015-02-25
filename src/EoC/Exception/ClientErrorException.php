<?php

/**
 * @file ClientErrorException.php
 * @brief This file contains the ClientErrorException class.
 * @details
 * @author Filippo F. Fadda
 */


namespace EoC\Exception;


/**
 * @brief Exception thrown when a client error is encountered (4xx codes)
 */
class ClientErrorException extends BadResponseException {}