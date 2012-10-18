<?php

//! @file DesignDoc.php
//! @brief This file contains the DesignDoc class.
//! @details
//! @author Filippo F. Fadda


namespace FFF\ElephantOnCouch;


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

  //const LIB_RW = "lib"; TODO This must be investigated and added.

  //@}

  // Used to know if the constructor has been already called.
  private static $initialized = FALSE;

  // Stores the names of the sections that belong to all the available handlers.
  private static $sections = [];

  // Stores the reserved words used by a Design Document.
  protected static $reservedWords = [
    self::LANGUAGE => NULL,
  ];


  public static function fromArray(array $array) {
    $instance = new self();
    $instance->meta = $array;
    $instance->meta[self::ID] = preg_replace('%\A_design/%m', "", $instance->meta[self::ID]);
    //$instance->initHandlers();

    print("\n\nMETADATI\n\n");
    print_r($instance->meta);
    return $instance;
  }


  //! @brief TODO
  public function __construct($name = "", $language = "php") {
    if (!empty($name))
      $this->meta[self::ID] = (string)$name;

    if (!empty($language))
      $this->meta[self::LANGUAGE] = (string)$language;

    if (!self::$initialized) {
      self::$initialized = TRUE;

      self::$reservedWords += parent::$reservedWords;

      self::scanForHandlers();
    }
  }


  //! @brief TODO
  private static function scanForHandlers() {
    foreach (glob(__DIR__."/Handlers/*.php") as $fileName) {
      //$className = preg_replace('/\.php\z/i', '', $fileName);
      $className = basename($fileName, ".php"); // Same like the above regular expression.

      if (class_exists($className) && array_key_exists("DesignHandler", class_parents($className)))
        self::$sections[] = $className::getSection();
    }
  }


  //! @brief Reset the list of handlers.
  public function resetHandlers() {
    foreach (self::$sections as $name) {
      if (array_key_exists($name, $this->meta))
        unset($this->meta[$name]);
    }
  }


  //! @brief TODO
  public function getHandlerAttributes($section, $name = "") {
    if (empty($name)) { // The handler doesn't have a name.
      if (array_key_exists($section, $this->meta))
        return $this->meta[$section];
      else
        throw new \Exception("Can't find '$section' handler in the design document.");
    }
    else { // The handler has a name.
      if (@array_key_exists($name, $this->meta[$section]))
        return $this->meta[$section][$name];
      else
        throw new \Exception("Can't find '$name' handler in the design document '$section' section.");
    }
  }


  //! @brief Adds a special handler to the design document.
  //! @details This method checks the existence of the property 'name', in fact a design document can have sections with
  //! multiple handlers, but in some cases there is one and only one handler per section, so that handler doesn't have a
  //! name.
  //! @exception TODO
  //! @exception TODO
  public function addHandler(DesignHandler $handler) {
    $section = $handler->getSection();

    if (property_exists($handler, "name")) {
      if (!$handler->isConsistent())
        throw new \Exception("'$handler->name' handler for the design document '$section' section is not consistent.");

      if (@array_key_exists($handler->name, $this->meta[$section]))
        throw new \Exception("'$handler->name' handler already exists for the design document '$section' section'.");
      else
        $this->meta[$section][$handler->name] = $handler->getAttributes();
    }
    else {
      if (!$handler->isConsistent())
        throw new \Exception("'$section' handler is not consistent.");

      if (array_key_exists($section, $this->meta))
        throw new \Exception("'$section' handler already exists for the design document.");
      else
        $this->meta[$section] = $handler;
    }
  }


  //! @brief Removes the handler.
  //! @exception TODO
  public function removeHandler($section, $name = "") {
    if (empty($name)) { // The handler doesn't have a name.
      if (array_key_exists($section, $this->meta))
        unset($this->meta[$section]);
      else
        throw new \Exception("Can't find '$section' handler in the design document.");
    }
    else { // The handler has a name.
      if (@array_key_exists($name, $this->meta[$section]))
        unset($this->meta[$section][$name]);
      else
        throw new \Exception("Can't find '$name' handler in the design document '$section' section.");
    }
  }


  public function getLanguage() {
    return $this->meta[self::LANGUAGE];
  }


  public function issetLanguage() {
    return $this->isMetadataPresent(self::LANGUAGE);
  }


  public function setLanguage($value) {
    $this->meta[self::LANGUAGE] = $value;
  }


  public function unsetLanguage() {
    unset($this->meta[self::LANGUAGE]);
  }

}

?>