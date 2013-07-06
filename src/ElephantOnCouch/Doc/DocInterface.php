<?php

//! @file DocInterface.php
//! @brief This file contains the DocInterface interface.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Doc;


//! @brief To become persistent a class must implement at least this interface.
//! @details For your convenience you would inherit your persistent classes from Doc or LocalDoc (in case of local
//! documents), because they already implements this interface. Else, if your model don't let you do that (PHP doesn't
//! support multiple inheritance), you might use the DocTrait, since it provides properties and methods to interact
//! with CouchDB. As last chance you can implement this interface.
//! @nosubgrouping
interface DocInterface {


  //! @brief Gets the document identifier.
  //! @return string
  public function getId();


  //! @brief Returns <i>true</i> if the document has an identifier, <i>false</i> otherwise.
  //! @return boolean
  public function issetId();


  //! Sets the document identifier. Mandatory and immutable.
  public function setId($value);


  //! @brief Sets the full name space class name into the the provided metadata into the metadata array.
  //! @details The method Couch.getDoc will use this to create an object of the same class you previously stored using
  //! Couch.saveDoc() method.
  //! @param[in] string $value The full namespace class name, like returned from get_class() function.
  public function setClass($value);


  //! @brief Sets the object type.
  //! @param[in] string $value Usually the class name purged of his namespace.
  public function setType($value);


  //! @brief Gets the document path.
  //! @details Returns an empty string for standard document, <i>_local/</i> for local document and <i>_design/</i> for
  //! design document.
  //! @return string
  public function getPath();


  //! @brief Returns the document representation as a JSON object.
  //! @return JSON object
  public function asJson();

}