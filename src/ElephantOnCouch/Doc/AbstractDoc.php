<?php

/**
 * @file AbstractDoc.php
 * @brief This file contains the AbstractDoc class.
 * @details
 * @author Filippo F. Fadda
 */


//! The CouchDB's documents namespace.
namespace ElephantOnCouch\Doc;


/**
 * @brief The abstract document is the ancestor of the other document classes.
 * @details This class encapsulates common properties and methods to provide persistence. Since it's an abstract class,
 * you can't create an instance of it.\n
 * You should instead inherit your persistent classes from the abstract Doc or LocalDoc (in case of local documents).
 * @attention Don't inherit from this superclass!
 * @nosubgrouping
 */
abstract class AbstractDoc implements IDoc {
  use TDoc;
}