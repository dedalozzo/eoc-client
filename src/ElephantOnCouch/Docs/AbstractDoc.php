<?php

//! @file AbstractDoc.php
//! @brief This file contains the AbstractDoc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Docs;


use Rest\Helpers\ArrayHelper;


//! @brief The abstract document is the ancestor of the other document classes. This class encapsulates common
//! properties and methods of every CouchDB document. Since it's an abstract class, you can't create an instance of it.
//! @nosubgrouping
abstract class AbstractDoc {
  use \ElephantOnCouch\Properties;

  //! @name Properties
  //! @brief Those are standard document's properties.
  //@{
  const ID = "_id"; //!< Document identifier. Mandatory and immutable.
  const REV = "_rev"; //!< The current MVCC-token/revision of this document. Mandatory and immutable.
  const DELETED = "_deleted"; //!< Indicates that this document has been deleted and previous revisions will be removed on next compaction run.
  const REVISIONS = "_revisions"; //!< The document revisions.
  //@}

  CONST DOC_CLASS = "doc_class"; //!< Special attribute used to store the concrete class name.

  // Stores the reserved words.
  protected static $reservedWords = [
    self::ID => NULL,
    self::REV => NULL,
    self::DELETED => NULL,
    self::REVISIONS,

    self::DOC_CLASS
  ];

  protected $meta;


  public function __construct() {
    $this->initMetadata();
  }


  private function initMetadata() {
    $this->meta = [];

    if ($this instanceof Doc || $this instanceof LocalDoc)
      $this->meta[self::DOC_CLASS] = get_class($this);
  }


  private function fixDocId() {
    if (isset($this->meta[self::ID])) {
      if ($this instanceof LocalDoc)
        $this->meta[self::ID] = preg_replace('%\A_local/%m', "", $this->meta[self::ID]);
      elseif ($this instanceof DesignDoc)
        $this->meta[self::ID] = preg_replace('%\A_design/%m', "", $this->meta[self::ID]);
    }
  }


  protected function isMetadataPresent($name) {
    return (array_key_exists($name, $this->meta)) ? TRUE : FALSE;
  }


  //! @brief Resets the metadata.
  public function resetMetadata() {
    unset($this->meta);
    $this->initMetadata();
  }


  //! Assigns the given associative array to the <i>$meta</i> array, the array that stores the document's metadata..
  //! @param[in] array $array An associative array.
  //! @exception Exception <c>Message: <i>\$array must be an associative array.</i></c>
  public function assignArray(array $array) {
    if (ArrayHelper::isAssociative($array)) {
      $this->meta = array_merge($array, $this->meta);
      $this->fixDocId();
    }
    else
      throw new \Exception("\$array must be an associative array.");
  }


  //! @brief Given an instance of a standard class, this function assigns every single object's property to the <i>$meta</i>
  //! array, the array that stores the document's metadata.
  public function assignObject(\stdClass $object) {
    $this->meta = array_merge(get_object_vars($object), $this->meta);
    $this->fixDocId();
  }


  //! @brief Returns the document representation as a JSON object.
  //! @return A JSON object.
  public function asJson() {
    return json_encode($this->meta);
  }


  //! @brief Adds a non standard reserved word.
  //! @param[in] string $word A reserved word.
  //! @exception Exception <c>Message: <i>The '\$word' reserved word is supported and already exists.</i></c>
  public static function addCustomReservedWord($word) {
    if (array_key_exists($word, static::$reservedWords))
      throw new \Exception("The '$word' reserved word is supported and already exists.");
    else
      static::$reservedWords[] = $word;
  }


  //! @brief Returns a list of reserved words that cannot be used.
  //! @return associative array
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
    $this->meta[self::ID] = (string)$value;
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


  public function getRevisions() {
    return (array_key_exists(self::REVISIONS, $this->meta)) ? $this->meta[self::REVISIONS] : NULL;
  }

}