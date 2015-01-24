<?php

/**
 * @file TDoc.php
 * @brief This file contains the TDoc trait.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Doc;


use ElephantOnCouch\Extension;
use ElephantOnCouch\Helper;


/**
 * @brief Implements the IDoc interface and add many functions.
 * @see AbstractDoc.dox
 */
trait TDoc {
  use Extension\TProperty;

  protected $meta = [];


  abstract protected function fixDocId();


  abstract public function getPath();


  public function resetMetadata() {
    unset($this->meta);
    $this->meta = [];
  }


  public function getMetadata($name) {
    return @$this->meta[$name];
  }


  public function isMetadataPresent($name) {
    return (array_key_exists($name, $this->meta)) ? TRUE : FALSE;
  }


  public function setMetadata($name, $value, $override = TRUE, $allowNull = TRUE) {
    if (is_null($value) && !$allowNull)
      return;

    if ($this->isMetadataPresent($name) && !$override)
      return;

    $this->meta[$name] = $value;
  }


  public function unsetMetadata($name) {
    if (array_key_exists($name, $this->meta))
      unset($this->meta[$name]);
  }


  public function setClass($value) {
    $this->meta['class'] = $value;
  }


  public function setType($value) {
    $this->meta['type'] = $value;
  }


  public function getType() {
    return $this->meta['type'];
  }


  public function hasType() {
    return FALSE;
  }


  public function assignJson($json) {
    $this->meta = array_merge($this->meta, Helper\ArrayHelper::fromJson($json, TRUE));
    $this->fixDocId();
  }


  public function assignArray(array $array) {
    if (Helper\ArrayHelper::isAssociative($array)) {
      $this->meta = array_merge($this->meta, $array);
      $this->fixDocId();
    }
    else
      throw new \InvalidArgumentException("\$array must be an associative array.");
  }


  public function assignObject(\stdClass $object) {
    $this->meta = array_merge($this->meta, get_object_vars($object));
    $this->fixDocId();
  }


  public function asJson() {
    return json_encode($this->meta);
  }


  public function asArray() {
    return $this->meta;
  }


  public function delete() {
    $this->meta['_deleted'] = "true";
  }


  public function isDeleted() {
    if ($this->meta['_deleted'] == "true")
      return TRUE;
    else
      return FALSE;
  }


  public function getRevisions() {
    return (array_key_exists('_revisions', $this->meta)) ? $this->meta['_revisions'] : NULL;
  }


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

}