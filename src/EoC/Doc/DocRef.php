<?php

/**
 * @file DocRef.php
 * @brief This file contains the DocRef class.
 * @details
 * @author Filippo F. Fadda
 */


namespace EoC\Doc;


use Meta\Extension;


/**
 * @brief This class represent a document reference and it's used in Couch::purgeDocs() method.
 * @details Instead to provide a full document, Couch.purgeDocs() accepts an array of DocRef. Every DocRef has an ID and
 * a list of revisions you can add calling addRev().
 */
final class DocRefsArray {
  use Extension\TProperty;

  private $id;
  private $revs;


  /**
   * @brief Adds a document revision reference.
   * @param[in] string $value A document revision.
   */
  public function addRev($value) {
    $this->revs[] = $value;
  }


  /**
   * @brief Returns a document reference.
   * @retval array An associative array
   */
  public function asArray() {
    $ref = [];
    $ref[$this->id] = $this->revs;
    return $ref;
  }


  //! @cond HIDDEN_SYMBOLS

  public function getId() {
    return $this->id;
  }


  public function issetId() {
    return isset($this->id);
  }


  public function setId($value) {
    if (!empty($value))
      $this->id = (string)$value;
    else
      throw new \Exception("\$id must be a non-empty string.");
  }


  public function unsetId() {
    unset($this->id);
  }

  //! @endcond

}