<?php

//! @file AbstractDoc.php
//! @brief This file contains the AbstractDoc class.
//! @details
//! @author Filippo F. Fadda


namespace FFF\ElephantOnCouch;


//! @brief The abstract document is the ancestor of the other document classes. This class encapsulates common
//! properties and methods of every CouchDB document. Since it's an abstract class, you can't create an instance of it.
//! @nosubgrouping
abstract class AbstractDoc {
  use Properties;

  //! @name Properties
  //! @brief Those are standard document's properties.
  //@{

  const ID = "_id"; //!< Document identifier. Mandatory and immutable.
  const REV = "_rev"; //!< The current MVCC-token/revision of this document. Mandatory and immutable.
  const DELETED = "_deleted"; //!< Indicates that this document has been deleted and previous revisions will be removed on next compaction run.
  const LOCAL_SEQUENCE = "_local_sequence"; //!< TODO I'm not sure this goes here. Probably on ReplicableDoc.

  //@}

  // Stores the reserved words.
  protected static $reservedWords = [
    self::ID => NULL,
    self::REV => NULL,
    self::DELETED => NULL,
    self::LOCAL_SEQUENCE => NULL //!< TODO I'm not sure this goes here. Probably on ReplicableDoc.
  ];

  protected $meta = [];


  protected function isMetadataPresent($name) {
    return (array_key_exists($name, $this->meta)) ? TRUE : FALSE;
  }


  //! @brief Resets the metadata.
  public function resetMetadata() {
    unset($this->meta);
    $this->meta = [];
  }

  //! @brief Given an instance of a standard class, this function assigns every single object's property to the <i>$meta</i>
  //! array, the array that stores the document's metadata.
  public function assignObject(\stdClass $object) {
    $this->meta = get_object_vars($object);
    print "METADATI\n"; // TODO Remove these lines.
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


  //! @brief Returns a list of reserved words that cannot be used.
  //! @return associate array
  public function getReservedWords() {
    return static::$reservedWords;
  }


  public function getId() {
    return $this->meta[self::ID];
  }


  public function issetId() {
    return $this->isMetadataPresent(self::ID);
  }


  public function setId($value) {
    if (is_string($value) && !empty($value))
      $this->meta[self::ID] = $value;
    else
      throw new \Exception("\$id must be a non-empty string.");
  }


  public function unsetId() {
    unset($this->meta[self::ID]);
  }


  public function getRev() {
    return $this->meta[self::REV];
  }


  public function issetRev() {
    return $this->isMetadataPresent(self::REV);
  }


  public function setRev($value) {
    // TODO regex on $value
    if (is_string($value) && !empty($value))
      $this->meta[self::ID] = $value;
    else
      throw new \Exception("\$id must be a non-empty string.");
  }


  public function unsetRev() {
    unset($this->meta[self::REV]);
  }


  public function isDeleted() {
    if ($this->meta[self::DELETED] == "true")
      return TRUE;
    else
      return FALSE;
  }


  public function getLocalSequence() {
    return $this->meta[self::LOCAL_SEQUENCE];
  }


  public function issetLocalSequence() {
    return $this->isMetadataPresent(self::LOCAL_SEQUENCE);
  }

}

?>