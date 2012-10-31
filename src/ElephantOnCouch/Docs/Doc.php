<?php

//! @file document.php
//! @brief This file contains the Doc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Docs;


//! @brief TODO
//! @nosubgrouping
class Doc extends ReplicableDoc {

  //! Creates and returns an instance of Doc class and initialize it with the associative array provided as input.
  //! @details Use this function when you need to modify an existence document.
  //! @param[in] array $array An associative array.
  //! @code
  //!   $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, "username", "password");
  //!   $couch->selectDb("my_database");
  //!   $doc = Doc::fromArray($couch->getDoc(ElephantOnCouch::STD_DOC_PATH, "my_view"));
  //! @endcode
  public static function fromArray(array $array) {
    $instance = new self();
    $instance->meta = $array;

    return $instance;
  }

}