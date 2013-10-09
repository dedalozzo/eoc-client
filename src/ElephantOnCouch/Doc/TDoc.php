<?php

//! @file TDoc.php
//! @brief This file contains the TDoc trait.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Doc;


use ElephantOnCouch\Helper;


//! @cond HIDDEN_SYMBOLS
trait TDoc {
  use Helper\Properties;

  protected $meta = [];


  //! @brief Removes tha path from the document identifier, because CouchDB returns it for local and design documents.
  //! @details Both LocalDoc and DesignDoc override this method.
  abstract protected function fixDocId();


  //! @brief Gets the document path.
  //! @details Both LocalDoc and DesignDoc override this method.
  //! @return string
  abstract public function getPath();


  //! @brief Resets the metadata.
  public function resetMetadata() {
    unset($this->meta);
    $this->meta = [];
  }


  //! @brief Checks the document for the given attribute.
  //! @return boolean
  public function isMetadataPresent($name) {
    return (array_key_exists($name, $this->meta)) ? TRUE : FALSE;
  }


  //! @brief Sets the full name space class name into the the provided metadata into the metadata array.
  //! @details The method Couch.getDoc will use this to create an object of the same class you previously stored using
  //! Couch.saveDoc() method.
  //! @param[in] string $value The full namespace class name, like returned from get_class() function.
  public function setClass($value) {
    $this->meta['class'] = $value;
  }


  //! @brief Sets the object type.
  //! @param[in] string $value Usually the lowercase class name purged of his namespace.
  public function setType($value) {
    $this->meta['type'] = $value;
  }


  //! @brief This implementation returns `false`.
  //! @return boolean
  public function hasType() {
    return FALSE;
  }


  //! Assigns the given associative array to the `$meta` array, the array that stores the document's metadata..
  //! @param[in] array $array An associative array.
  public function assignArray(array $array) {
    if (Helper\ArrayHelper::isAssociative($array)) {
      $this->meta = array_merge($array, $this->meta);
      $this->fixDocId();
    }
    else
      throw new \InvalidArgumentException("\$array must be an associative array.");
  }


  //! @brief Given an instance of a standard class, this function assigns every single object's property to the `$meta`
  //! array, the array that stores the document's metadata.
  public function assignObject(\stdClass $object) {
    $this->meta = array_merge(get_object_vars($object), $this->meta);
    $this->fixDocId();
  }


  //! @brief Returns the document representation as a JSON object.
  //! @return JSON object
  public function asJson() {
    return json_encode($this->meta);
  }


  //! @brief Returns the document representation as an associative array.
  //! @return associative array
  public function asArray() {
    return $this->meta;
  }


  public function getId() {
    return $this->meta['_id'];
  }


  public function issetId() {
    return isset($this->meta['_id']);
  }


  public function setId($value) {
    if (!empty($value))
      $this->meta['_id'] = (string)$value;
    else
      throw new \Exception("\$id must be a non-empty string.");
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
    $this->meta['_id'] = (string)$value;
  }


  public function unsetRev() {
    if ($this->isMetadataPresent('_rev'))
      unset($this->meta['_rev']);
  }


  //! @brief Marks the document as deleted. To be effected the document must be saved.
  public function delete() {
    $this->meta['_deleted'] == "true";
  }


  //! @brief Indicates that this document has been deleted and previous revisions will be removed on next compaction run.
  public function isDeleted() {
    if ($this->meta['_deleted'] == "true")
      return TRUE;
    else
      return FALSE;
  }


  //! @brief The document revisions.
  public function getRevisions() {
    return (array_key_exists('_revisions', $this->meta)) ? $this->meta['_revisions'] : NULL;
  }

}
//! @endcond