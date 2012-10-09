<?php

//! @file AbstractDoc.class.php
//! @brief This file contains the AbstractDoc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief The abstract document is the ancestor of the other document classes. This class encapsulates common
//! properties and methods of every CouchDB document. Since it's an abstract class, you can't create an instance of it.
//! @nosubgrouping
abstract class AbstractDoc {
  use Properties;

  //! @name Reserved Words
  //! @brief You can't use a reserved word to name a document's attribute.
  //@{

  const ID_RW = "_id"; //!< Document identifier. Mandatory and immutable.
  const REV_RW = "_rev"; //!< The current MVCC-token/revision of this document. Mandatory and immutable.
  const DELETE_RW = "_deleted"; //!< Indicates that this document has been deleted and previous revisions will be removed on next compaction run.
  const LOCAL_SEQUENCE_RW = "_local_sequence"; //!< TODO I'm not sure this goes here. Probably on ReplicableDoc.

  //@}

  // Stores the reserved words used by a Local Document.
  protected static $reservedWords = array(
    self::ID_RW => NULL,
    self::REV_RW => NULL,
    self::DELETE_RW => NULL,
    self::LOCAL_SEQUENCE_RW => NULL //!< TODO I'm not sure this goes here. Probably on ReplicableDoc.
  );

  protected $meta = array();


  protected function isMetadataPresent($name) {
    if (array_key_exists($name, $this->meta))
      return TRUE;
    else
      return FALSE;
  }


  //! @brief Reset the metadata.
  public function resetMetadata() {
    unset($this->meta);
    $this->meta = array();
  }

  //! @brief Given an instance of a standard class, this function assigns every single object's property to the <i>$meta</i>
  //! array, the array that stores the document's metadata.
  public function assignObject(\stdClass $object) {
    $this->meta = get_object_vars($object);
    print "METADATI\n";
    print_r($this->meta);
  }


  //! @brief Returns the document representation as a JSON object.
  //! @return A JSON object.
  public function asJson() {
    return json_encode($this->meta);
  }


  //! @brief Adds a non standard reserved word.
  //! @param[in] string $word
  //! @exception Exception <c>Message: <i>The '\$word' reserved word is supported and already exists.</i></c>
  public static function addCustomReservedWord($word) {
    if (array_key_exists($word, static::$reservedWords))
      throw new \Exception("The '$word' reserved word is supported and already exists.");
    else
      static::$reservedWords[] = $word;
  }


  //! @brief TODO
  public function getReservedWords() {
    return static::$reservedWords;
  }


  public function getId() {
    return $this->meta[self::ID_RW];
  }


  public function issetId() {
    return $this->isMetadataPresent(self::ID_RW);
  }


  public function setId($value) {
    if (is_string($value) && !empty($value))
      $this->meta[self::ID_RW] = $value;
    else
      throw new \Exception("\$id must be a non-empty string.");
  }


  public function unsetId() {
    unset($this->meta[self::ID_RW]);
  }


  public function getRev() {
    return $this->meta[self::REV_RW];
  }


  public function issetRev() {
    return $this->isMetadataPresent(self::REV_RW);
  }


  public function setRev($value) {
    // TODO regex on $value
    if (is_string($value) && !empty($value))
      $this->meta[self::ID_RW] = $value;
    else
      throw new \Exception("\$id must be a non-empty string.");
  }


  public function unsetRev() {
    unset($this->meta[self::REV_RW]);
  }


  public function isDeleted() {
    if ($this->meta[self::DELETE_RW] == "true")
      return TRUE;
    else
      return FALSE;
  }


  public function getLocalSequence() {
    return $this->meta[self::LOCAL_SEQUENCE_RW];
  }


  public function issetLocalSequence() {
    return $this->isMetadataPresent(self::LOCAL_SEQUENCE_RW);
  }

}

?>