<?php

/**
 * @file UnknownResponseException.php
 * @brief This file contains the UnknownResponseException class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Exception;


/**
 * @brief Exception thrown when an unknown response is encountered (> 600 codes)
 */
class UnknownResponseException extends BadResponseException {}