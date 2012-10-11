<?php

//! @file DesignDoc.php
//! @brief This file contains the DesignDoc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief A design document is a special CouchDB document where views, updates, rewrites and many others handlers are
//! stored.
//! @see http://guide.couchdb.org/editions/1/en/design.html
//! @nosubgrouping
final class DesignDoc extends ReplicableDoc {

  //! @name Design Document Properties
  //@{

  //! @brief The purpose of this property is to specify the programming language used to write the handlers' closures.
  //! @details CouchDB will automatilly use the right interpreter for the various handlers stored into this design document.
  const LANGUAGE = "language";

  //! @brief TODO
  //! @details A design document may define a member function called "validate_doc_update". Requests to create or update a document
  //! are validated against every "validate_doc_update" function defined in the database. The validation functions are
  //! executed in an unspecified order. A design document can contain only one validation function. Errors are thrown as
  //! JavaScript objects.
  const VALIDATE_DOC_UPDATE = "validate_doc_update";
  //const LIB_RW = "lib"; TODO this must be investigate and added

  //@}


  //! @name Handlers
  //@{

  const VIEWS_HDLR = "views";
  const SHOWS_HDLR = "shows";
  const UPDATES_HDLR = "updates";
  const REWRITE_HDLR = "rewrites";
  const FILTERS_HDLR = "filters";

  //@}


  // Stores the reserved words used by a Design Document.
  protected static $reservedWords = [
    self::LANGUAGE_RW => NULL,
    self::VALIDATE_DOC_UPDATE_RW => NULL
  ];


  // Stores the reserved words used by a Design Document.
  protected static $handlers = [
  ];


  // Stores the special handlers.
  private $handlers = array();


  public static function fromArray(array $array) {
    $instance = new self();
    $instance->meta = $array;
    $instance->meta[self::ID_RW] = preg_replace('%\A_design/%m', "", $instance->meta[self::ID_RW]);
    //$instance->initHandlers();

    print("\n\nMETADATI\n\n");
    print_r($instance->meta);
    return $instance;
  }


  //! @brief TODO
  public function __construct($name = "", $language = "php") {
    if (!empty($name))
      $this->meta[self::ID_RW] = (string)$name;

    if (!empty($language))
      $this->meta[self::LANGUAGE_RW] = (string)$language;

    self::$reservedWords += parent::$reservedWords;
  }



  private function scanHandlers() {

    foreach (glob(__DIR__."/Handlers/*.php") as $fileName) {
      $className = preg_replace('/\.php\z/i', '', $fileName);

      if (class_exists($className))
        $handlerNames = $className::getPippo();
    }


  }


  private function registerHandler() {


  }


  //! @brief Reset the list of handlers.
  public function resetHandlers() {
    unset($this->handlers);
    $this->handlers = array();
  }


  public function setHandlers(array $value) {
    $this->handlers = $value;
  }


  public function getHandlers() {
    return $this->handlers;
  }


  //! @brief TODO
  public function getHandler($section, $name) {
    if (@array_key_exists($name, $this->handlers[$section]))
      return $this->handlers[$section][$name];
    else
      throw new \Exception("Can't find '$name' handler in the design document '$section' section.");
  }


  //! @brief Adds a special handler to the design document.
  //! @details TODO
  //! @exception TODO
  //! @exception TODO
  public function addHandler(DesignHandler $handler) {
    $section = $handler->section;

    if (@array_key_exists($handler->name, $this->handlers[$section]))
      throw new \Exception("'$handler->name' handler already exists for the design document '$section' section'. Please, remove it and try again.");
    else
      $this->handlers[$section][$handler->name] = $handler;
  }


  //! @brief TODO
  //! @exception TODO
  public function removeHandler($section, $name) {
    if (@array_key_exists($name, $this->handlers[$section]))
      unset($this->handlers[$section][$name]);
    else
      throw new \Exception("Can't find '$name' handler in the design document '$section' section.");
  }


  //! @brief Returns the document representation as a JSON object.
  //! @return A JSON object.
  //! @exception TODO
  public function asJson() {
    foreach ($this->handlers as $section => $handlersPerSection) {
      foreach ($handlersPerSection as $handler)
        $this->meta[$section][$handler->name] = $handler->asArray();
    }

    return json_encode($this->meta);
  }


  public function getLanguage() {
    return $this->meta[self::LANGUAGE_RW];
  }


  public function issetLanguage() {
    return $this->isMetadataPresent(self::LANGUAGE_RW);
  }


  public function setLanguage($value) {
    $this->meta[self::LANGUAGE_RW] = $value;
  }


  public function unsetLanguage() {
    unset($this->meta[self::LANGUAGE_RW]);
  }


  public function getValidateDocUpdate() {
    return $this->meta[self::VALIDATE_DOC_UPDATE_RW];
  }


  public function issetValidateDocUpdate() {
    return $this->isMetadataPresent(self::LANGUAGE_RW);
  }


  public function setValidateDocUpdate($fn) {
    $this->meta[self::VALIDATE_DOC_UPDATE_RW] = $fn;
  }


  public function unsetValidateDocUpdate() {
    unset($this->meta[self::VALIDATE_DOC_UPDATE_RW]);
  }

}

?>