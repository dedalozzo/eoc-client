<?php

/**
 * @file ValidateDocUpdateHandler.php
 * @brief This file contains the ValidateDocUpdateHandler class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Handler;


/**
 * @brief This handler let you define a validation function, so every request to create or update a document is validated
 * against the function you provided.
 * @details A design document may define a member function called `validate_doc_update`. Requests to create or update
 * a document are validated against every `validate_doc_update` function defined in the database. The validation
 * functions are executed in an unspecified order. A design document can contain only one validation function. Errors are
 * thrown as JavaScript objects.
 * @nosubgrouping
 * @todo Check this class.
 */
final class ValidateDocUpdateHandler extends DesignHandler {
  const VALIDATE_DOC_UPDATE = "validate_doc_update";

  private $validateDocUpdate = "";


  public static function getSection() {
    return self::VALIDATE_DOC_UPDATE;
  }


  public function isConsistent() {
    return $this->issetValidateDocUpdate();
  }


  public function asArray() {
    return $this->validateDocUpdate;
  }


  public function getValidateDocUpdate() {
    return $this->validateDocUpdate;
  }


  public function issetValidateDocUpdate() {
    return (!empty($this->validateDocUpdate)) ? TRUE : FALSE;
  }


  public function setValidateDocUpdate($value) {
    $this->validateDocUpdate = (string)$value;
  }


  public function unsetValidateDocUpdate() {
    $this->validateDocUpdate = "";
  }

}