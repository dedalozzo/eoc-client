<?php

//! @file ViewHandler.php
//! @brief This file contains the ViewHandler class.
//! @details
//! @author Filippo F. Fadda


namespace FFF\ElephantOnCouch\Handlers;


use Lint\Lint;
use ElephantOnCouch\DesignHandler;


//! @brief This handler let you create a CouchDB view.
//! @details Views are the primary tool used for querying and reporting on CouchDB databases. Views are managed by a
//! special server. Default server implementation uses JavaScript, that's why you have to write views in JavaScript
//! language. This handler instead let you write your views directly in PHP.
//! To create a permanent view, the functions must first be saved into special design document. Every design document has
//! a special 'views' attribute, that stores mandatory map function and an optional reduce function. Using this handler
//! you can write these functions directly in PHP.
//! All the views in one design document are indexed whenever any of them gets queried.
//! @nosubgrouping
final class ViewHandler extends DesignHandler {
  const VIEWS = "views";
  const OPTIONS = "options";
  const MAP = "map";
  const REDUCE = "reduce";

  private $name;

  private $options = [];

  //! @name Properties
  //@{

  //! @brief Stores the map function.
  //! @details Contains the function implementation provided by the user. You can have multiple views in a design document
  //! and for every single view you can have only one map function. The map function is a closure.
  //! The closure must be declared like:
  //! @code
  //! function($doc) use ($emit) {
  //!   ...
  //!
  //!   $emit($key, $value);
  //! }
  //! @endcode
  //! To emit your record you must call the <i>$emit</i> closure.
  private $mapFn = "";

  //! @brief Stores the reduce function.
  //! @details Contains the function implementation provided by the user. You can have multiple views in a design document
  //! and for every single view you can have only one reduce function. The reduce function is a closure.
  //! The closure must be declared like:
  //! @code
  //! function($doc) use ($emit) {
  //!   ...
  //!
  //!   $emit($key, $value);
  //! }
  //! @endcode
  //! To emit your record you must call the <i>$emit</i> closure.
  private $reduceFn = "";

  //@}


  //! @brief Creates a ViewHandler class instance.
  //! @param[in] string $name Handler name.
  public function __construct($name) {
    $this->setName($name);
  }


  //! @brief Resets the options.
  public function reset() {
    unset($this->options);
    $this->options = [];

    $this->mapFn = "";
    $this->reduceFn = "";
  }


  public function getName() {
    return $this->name;
  }


  public function setName($value) {
    $this->name = (string)$value;
  }


  public static function getSection() {
    return self::VIEWS;
  }


  public function isConsistent() {
    return (!empty($this->name) && !empty($this->mapFn)) ? TRUE : FALSE;
  }


  public function getAttributes() {
    $view = [];
    $view[self::MAP] = $this->mapFn;

    if (!empty($this->reduceFn))
      $view[self::REDUCE] = $this->reduceFn;

    if (!empty($this->options))
      $view[self::OPTIONS] = $this->options;

    return $view;
  }


  public function getMapFn() {
    return $this->mapFn;
  }


  public function setMapFn($closure) {
    Lint::checkSourceCode($closure);

    if (preg_match('/function\s*\(\s*\$doc\)\s*use\s*\(\$emit\)\s*\{[\W\w]*\};\z/m', $closure))
      $this->mapFn = $closure;
    else
      throw new \Exception("The \$closure must be defined like: function(\$doc) use (\$emit) { ... };");
  }


  public function getReduceFn() {
    return $this->reduceFn;
  }


  public function setReduceFn($closure) {
    Lint::checkSourceCode($closure);

    if (preg_match('/function\s*\(\s*\$key\s*,\s*\$value\)\s*\{[\W\w]*\};\z/m', $closure))
      $this->reduceFn = $closure;
    else
      throw new \Exception("The \$closure must be defined like: function(\$key, \$value) { ... };");
  }


  //! @brief Makes documents' local sequence numbers available to map functions as a '_local_seq' document property.
  public function includeLocalSeq() {
    $this->options['local_seq'] = 'true';
  }


  //! @brief Causes map functions to be called on design documents as well as regular documents.
  public function includeDesignDocs() {
    $this->options['include_design'] = 'true';
  }

}

?>