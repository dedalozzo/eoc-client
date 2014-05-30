<?php

/**
 * @file DesignDoc.php
 * @brief This file contains the DesignDoc class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Doc;


use ElephantOnCouch\Handler;


/**
 * @brief A design document is a special CouchDB document where views, updates, rewrites and many others handlers are
 * stored.
 * @see http://guide.couchdb.org/editions/1/en/design.html
 * @nosubgrouping
 */
final class DesignDoc extends Doc {

  // Stores the names of the sections that belong to all the available handlers.
  private $sections = [];


  public function __construct() {
    $this->loadHandlers();
  }


  /**
   * @brief Creates an instance of DesignDoc class.
   * @param[in] string $name The design document name.
   * @param[in] string $language The programming language used by the design document for his handlers.
   * @return DesignDoc An instance of the class.
   */
  public static function create($name, $language = "php") {
    $instance = new self();

    $instance->setId($name);
    $instance->setLanguage($language);

    return $instance;
  }


  // Load the handlers.
  private function loadHandlers() {
    $this->sections[Handler\FilterHandler::getSection()] = NULL;
    $this->sections[Handler\ListHandler::getSection()] = NULL;
    $this->sections[Handler\RewriteHandler::getSection()] = NULL;
    $this->sections[Handler\ShowHandler::getSection()] = NULL;
    $this->sections[Handler\UpdateHandler::getSection()] = NULL;
    $this->sections[Handler\ValidateDocUpdateHandler::getSection()] = NULL;
    $this->sections[Handler\ViewHandler::getSection()] = NULL;
  }


  /**
   * @brief Removes `_design/` from he document identifier.
   */
  protected function fixDocId() {
    if (isset($this->meta['_id']))
      $this->meta['_id'] = preg_replace('%\A_design/%m', "", $this->meta['_id']);
  }


  /**
   * @brief Design documents don't have a class, so we don't provide an implementation.
   */
  public function setClass($value) {}


  /**
   * @brief Design documents don't have a type, so we don't provide an implementation.
   */
  public function setType($value) {}


  /**
   * @brief Gets the document path: `_design/`.
   * @return string
   */
  public function getPath() {
    return "_design/";
  }


  /**
   * @brief Reset the list of handlers.
   */
  public function resetHandlers() {
    foreach ($this->sections as $name => $value) {
      if (array_key_exists($name, $this->meta))
        unset($this->meta[$name]);
    }
  }


  /**
   * @brief Given a design document section (ex. views, updates, filters, etc.) and an optional handler's name (because
   * the handler couldn't have a name), returns the
   * @param[in] string $section The section name.
   * @param[in] string $name (optional) The handler name.
   */
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


  /**
   * @brief Adds a special handler to the design document.
   * @details This method checks the existence of the property `$name`, in fact a design document can have sections
   * with multiple handlers, but in some cases there is one and only one handler per section, so that handler doesn't
   * have a name.
   * @param[in] DesignIHandler $handler An instance of a subclass of the abstract class DesignIHandler.
   */
  public function addHandler(Handler\DesignIHandler $handler) {
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


  /**
   * @brief Removes the handler.
   * @details Some handlers belong to a section. For example a view ViewHandler belongs to the 'views' section. To specify
   * the appropriate section name, you shoudl use the static method `getSection` available for every handler
   * implementation.
   * @param[in] string $section The section's name (views, updates, shows, filters, etc).
   * @param[in] string $name (optional) The handler's name.
   */
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


  //! @cond HIDDEN_SYMBOLS

  public function getLanguage() {
    return $this->meta['language'];
  }


  public function issetLanguage() {
    return isset($this->meta['language']);
  }


  public function setLanguage($value) {
    if (!empty($value))
      $this->meta['language'] = strtolower((string)$value);
    else
      throw new \InvalidArgumentException("\$language must be a non-empty string.");
  }


  public function unsetLanguage() {
    if ($this->isMetadataPresent('language'))
      unset($this->meta['language']);
  }

  //! @endcond

}