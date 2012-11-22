<?php

//! @file DesignDoc.php
//! @brief This file contains the DesignDoc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Doc;


use ElephantOnCouch\Handler;
use ElephantOnCouch\Handler\DesignHandler;


//! @brief A design document is a special CouchDB document where views, updates, rewrites and many others handlers are
//! stored.
//! @see http://guide.couchdb.org/editions/1/en/design.html
//! @nosubgrouping
final class DesignDoc extends ReplicableDoc {

  //! @name Design Document Properties
  //@{

  //! @brief The purpose of this property is to specify the programming language used to write the handlers' closures.
  //! @details CouchDB will automatically use the right interpreter for the various handlers stored into this design document.
  const LANGUAGE = "language";

  //const LIB_RW = "lib"; TODO This must be investigated and added or an handler must be created for it.

  //@}

  // Used to know if the constructor has been already called.
  private static $initialized = FALSE;

  // Stores the names of the sections that belong to all the available handlers.
  private static $sections = [];

  // Stores the reserved words used by a Design Document.
  protected static $reservedWords = [
    self::LANGUAGE => NULL,
  ];


  public function __construct() {
    parent::__construct();

    if (!self::$initialized) {
      self::$initialized = TRUE;

      self::$reservedWords += parent::$reservedWords;

      self::scanForHandlers();
    }
  }

  //! @brief Creates an instance of DesignDoc class.
  //! @param[in] string $name The design document name.
  //! @param[in] string $name The programming language used by the design document for his handlers.
  public static function create($name, $language = "php") {
    $instance = new self();

    $instance->setId($name);
    $instance->setLanguage($language);

    return $instance;
  }


  //! @brief Scans the handlers' directory.
  //! @details Every CouchDB's handler is stored in a particular design document section. Every class that extends the
  //! abstract handler DesignHandler, must implement a static method to return his own section. These sections are stored
  //! in a static class property to be used later.
  private static function scanForHandlers() {
    foreach (glob(dirname(__DIR__)."/Handler/*.php") as $fileName) {
      //$className = preg_replace('/\.php\z/i', '', $fileName);
      $className = "ElephantOnCouch\\Handler\\".basename($fileName, ".php"); // Same like the above regular expression.

      if (class_exists($className) && array_key_exists("ElephantOnCouch\\Handler\\DesignHandler", class_parents($className)))
        self::$sections[$className::getSection()] = NULL;
    }

  }


  //! @brief Reset the list of handlers.
  public function resetHandlers() {
    foreach (self::$sections as $name => $value) {
      if (array_key_exists($name, $this->meta))
        unset($this->meta[$name]);
    }
  }


  //! @brief Given a design document section (ex. views, updates, filters, etc.) and an optional handler's name (because
  //! the handler couldn't have a name), returns the
  //! @param[in] string $section The section name.
  //! @param[in] string $name (optional) The handler name.
  //! @exception Exception <c>Message: <i>Can't find handler in the design document.</i></c>
  //! @exception Exception <c>Message: <i>Can't find handler in the design document section.</i></c>
  public function getHandlerAttributes($section, $name = "") {
    if (empty($name)) { // The handler doesn't have a name.
      if (array_key_exists($section, $this->meta))
        return $this->meta[$section];
      else
        throw new \Exception(sprintf("Can't find '%s' handler in the design document.", $section));
    }
    else { // The handler has a name.
      if (@array_key_exists($name, $this->meta[$section]))
        return $this->meta[$section][$name];
      else
        throw new \Exception(sprintf("Can't find '%s' handler in the design document '%s' section.", $name, $section));
    }
  }


  //! @brief Adds a special handler to the design document.
  //! @details This method checks the existence of the property 'name', in fact a design document can have sections with
  //! multiple handlers, but in some cases there is one and only one handler per section, so that handler doesn't have a
  //! name.
  //! @param[in] DesignHandler $handler An instance of a subclass of the abstract class DesignHandler.
  //! @exception Exception <c>Message: <i>The handler is not consistent.</i></c>
  //! @exception Exception <c>Message: <i>The handler already exists.</i></c>
  public function addHandler(DesignHandler $handler) {
    $section = $handler->getSection();

    if (property_exists($handler, "name")) {
      if (!$handler->isConsistent())
        throw new \Exception(sprintf("The '%s' handler '%s' is not consistent.", $section, $handler->name));

      if (@array_key_exists($handler->name, $this->meta[$section]))
        throw new \Exception(sprintf("The '%s' handler '%s' already exists.", $section, $handler->name));
      else
        $this->meta[$section][$handler->name] = $handler->asArray();
    }
    else {
      if (!$handler->isConsistent())
        throw new \Exception(sprintf("The '%s' handler is not consistent.", $section));

      if (array_key_exists($section, $this->meta))
        throw new \Exception(sprintf("The '%s' handler already exists.", $section));
      else
        $this->meta[$section] = $handler;
    }
  }


  //! @brief Removes the handler.
  //! @details Some handlers belong to a section. For example a view ViewHandler belongs to the 'views' section. To specify
  //! the appropriate section name, you shoudl use the static method <i>getSection</i> available for every handler
  //! implementation.
  //! @param[in] string $section The section's name (views, updates, shows, filters, etc).
  //! @param[in] string $name (optional) The handler's name.
  //! @exception Exception <c>Message: <i>Can't find the handler.</i></c>
  public function removeHandler($section, $name = "") {
    if (empty($name)) { // The handler doesn't have a name.
      if (array_key_exists($section, $this->meta))
        unset($this->meta[$section]);
      else
        throw new \Exception(sprintf("Can't find the '%s' handler.", $section));
    }
    else { // The handler has a name.
      if (@array_key_exists($name, $this->meta[$section]))
        unset($this->meta[$section][$name]);
      else
        throw new \Exception(sprintf("Can't find the '%s' handler '%s'", $section, $name));
    }
  }


  public function getLanguage() {
    return $this->meta[self::LANGUAGE];
  }


  public function issetLanguage() {
    return isset($this->meta[self::LANGUAGE]);
  }


  public function setLanguage($value) {
    if (!empty($value))
      $this->meta[self::LANGUAGE] = (string)$value;
    else
      throw new \InvalidArgumentException("\$language must be a non-empty string.");
  }


  public function unsetLanguage() {
    if ($this->isMetadataPresent(self::LANGUAGE))
      unset($this->meta[self::LANGUAGE]);
  }

}