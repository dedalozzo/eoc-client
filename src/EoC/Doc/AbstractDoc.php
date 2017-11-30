<?php

/**
 * @file AbstractDoc.php
 * @brief This file contains the AbstractDoc class.
 * @details
 * @author Filippo F. Fadda
 */


//! The CouchDB's documents namespace
namespace EoC\Doc;


use Meta\MetaClass;


/**
 * @brief The abstract document is the ancestor of the other document classes.
 * @details This class encapsulates common properties and methods to provide persistence. Since it's an abstract class,
 * you can't create an instance of it.\n
 * You should instead inherit your persistent classes from the abstract Doc or LocalDoc (in case of local documents).
 * @attention Don't inherit from this superclass!
 * @nosubgrouping
 *
 * @cond HIDDEN_SYMBOLS
 *
 * @property string $id;
 * @property string $rev;
 *
 * @endcond
 */
abstract class AbstractDoc extends MetaClass implements IDoc {


  /**
   * @brief Removes tha path from the document identifier, because CouchDB returns it for local and design documents.
   * @details Both LocalDoc and DesignDoc override this method.
   */
  abstract protected function fixDocId();


  /**
   * @brief Sets the object class.
   * @param[in] string $value The instance class.
   */
  public function setClass($value) {
    $this->meta['class'] = $value;
  }


  /**
   * @copydoc IDoc::setType()
   */
  public function setType($value) {
    $this->meta['type'] = $value;
  }


  /**
   * @brief Returns the object type.
   * @retval string
   */
  public function getType() {
    return $this->meta['type'];
  }


  /**
   * @copydoc IDoc::hasType()
   */
  public function hasType() {
    return FALSE;
  }


  /**
   * @copydoc IDoc::getPath()
   */
  abstract public function getPath();


  public function assignJson($json) {
    parent::assignJson($json);
    $this->fixDocId();
  }


  public function assignArray(array $array) {
    parent::assignArray($array);
    $this->fixDocId();
  }


  public function assignObject(\stdClass $object) {
    parent::assignObject($object);
    $this->fixDocId();
  }


  /**
   * @brief Marks the document as deleted. To be effected the document must be saved.
   */
  public function delete() {
    $this->meta['_deleted'] = TRUE;
  }


  /**
   * @brief Indicates that this document has been deleted and previous revisions will be removed on next compaction run.
   */
  public function isDeleted() {
    return $this->meta['_deleted'];
  }


  /**
   * @brief Gets the document revisions.
   */
  public function getRevisions() {
    return (array_key_exists('_revisions', $this->meta)) ? $this->meta['_revisions'] : NULL;
  }


  //! @cond HIDDEN_SYMBOLS

  public function getId() {
    return $this->meta['_id'];
  }


  public function issetId() {
    return isset($this->meta['_id']);
  }


  public function setId($value) {
    $this->meta['_id'] = (string)$value;
  }


  public function unsetId() {
    if ($this->isMetadataPresent('_id'))
      unset($this->meta['_id']);
  }


  public function getRev() {
    return $this->meta['_rev'];
  }


  public function issetRev() {
    return isset($this->meta['_rev']);
  }


  public function setRev($value) {
    $this->meta['_rev'] = (string)$value;
  }


  public function unsetRev() {
    if ($this->isMetadataPresent('_rev'))
      unset($this->meta['_rev']);
  }

  //! @endcond

}