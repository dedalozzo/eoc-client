<?php

/**
 * @file IDoc.php
 * @brief This file contains the IDoc interface.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Doc;


/**
 * @brief To become persistent a class must implement at least this interface.
 * @details For your convenience you would inherit your persistent classes from Doc or LocalDoc (in case of local
 * documents), because they already implements this interface. Else, if your model don't let you do that (PHP doesn't
 * support multiple inheritance), you might use the TDoc, since it provides properties and methods to interact
 * with CouchDB. As last chance you can implement this interface.
 * @nosubgrouping
 */
interface IDoc {

  /**
   * @brief Sets the object type.
   * @param[in] string $value Usually the class name purged of his namespace.
   */
  function setType($value);


  /**
   * @brief Returns `true` if your document class already defines his type internally, `false` otherwise.
   * @details Sometime happens you have two classes with the same name but located under different namespaces. In case,
   * you should provide a type yourself for at least one of these classes, to avoid Couch::saveDoc() using the same type
   * for both. Default implementation should return `false`.
   * @return boolean
   */
  function hasType();


  /**
   * @brief Gets the document identifier.
   * @return string
   */
  function getId();


  /**
   * @brief Returns `true` if the document has an identifier, `false` otherwise.
   * @return boolean
   */
  function issetId();


  /**
   * @brief Sets the document identifier. Mandatory and immutable.
   */
  function setId($value);


  /**
   * @brief Unset the document identifier.
   */
  function unsetId();


  /**
   * @brief Sets the full name space class name into the the provided metadata into the metadata array.
   * @details The method Couch.getDoc will use this to create an object of the same class you previously stored using
   * Couch::saveDoc() method.
   * @param[in] string $value The full namespace class name, like returned from get_class() function.
   */
  function setClass($value);


  /**
   * @brief Gets the document path.
   * @details Returns an empty string for standard document, `_local/` for local document and `_design/` for
   * design document.
   * @return string
   */
  function getPath();


  /**
   * @brief Returns the document representation as a JSON object.
   * @return JSON object
   */
  function asJson();


  /**
   * @brief Returns the document representation as an associative array.
   * @return associative array
   */
  public function asArray();

}