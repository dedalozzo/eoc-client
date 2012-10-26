<?php

//! @file ViewHandler.php
//! @brief This file contains the ViewHandler class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Handlers;


use Lint\Lint;


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
  const MAP_REGEX = '/function\s*\(\s*\$doc\)\s*use\s*\(\$emit\)\s*\{[\W\w]*\};\z/m';
  const MAP_DEFINITION = "function(\$doc) use (\$emit) { ... };";

  const REDUCE = "reduce";
  const REDUCE_REGEX = '/function\s*\(\s*\$key\s*,\s*\$value\,\s*\$rereduce\)\s*\{[\W\w]*\};\z/m';
  const REDUCE_DEFINITION = "function(\$key, \$value, \$rereduce) { ... };";

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


  public function getName() {
    return $this->name;
  }


  public function setName($value) {
    $this->name = (string)$value;
  }


  //! @brief Resets the options.
  public function reset() {
    unset($this->options);
    $this->options = [];

    $this->mapFn = "";
    $this->reduceFn = "";
  }


  public static function getSection() {
    return self::VIEWS;
  }


  public function isConsistent() {
    return (!empty($this->name) && !empty($this->mapFn)) ? TRUE : FALSE;
  }


  public static function checkFn($fnImpl, $fnDef, $fnRegex) {
    Lint::checkSourceCode($fnImpl);

    if (preg_match($fnRegex, $fnImpl) === FALSE)
      throw new \Exception("The \$closure must be defined like: $fnDef");
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


  //! @brief Makes documents' local sequence numbers available to map functions as a '_local_seq' document property.
  public function includeLocalSeq() {
    $this->options['local_seq'] = 'true';
  }


  //! @brief Causes map functions to be called on design documents as well as regular documents.
  public function includeDesignDocs() {
    $this->options['include_design'] = 'true';
  }


  //! @brief Sets the reduce function to the built-in <i>_count</i> function provided by CouchDB.
  //! @details The buil-in <i>_count</i> reduce function will be probably the most common reduce function you'll use.
  //! This function returns the number of mapped values in the set.
  public function useBuiltInReduceFnCount() {
    $this->reduceFn = "_count";
  }


  //! @brief Sets the reduce function to the built-in <i>_sum</i> function provided by CouchDB.
  //! @details The buil-in <i>_sum</i> reduce function will return a sum of mapped values. As with all reductions, you
  //! can either get a sum of all values grouped by keys or part of keys. You can control this behaviour when you query
  //! the view, using an instance of <i>ViewQueryArgs</i> class, in particular with methods <i>groupReults</i> and <i>setGroupLevel</i>.
  //! @warning The buil-in <i>_sum</i> reduce function requires all mapped values to be numbers.
    public function useBuiltInReduceFnSum() {
    $this->reduceFn = "_sum";
  }


  //! @brief Sets the reduce function to the built-in <i>_stats</i> function provided by CouchDB.
  //! @details The buil-in <i>_stats</i> reduce function returns an associative array containing the sum, count, minimum,
  //! maximum, and sum over all square roots of mapped values.
  public function useBuiltInReduceFnStats() {
    $this->reduceFn = "_stats";
  }


  public function getMapFn() {
    return $this->mapFn;
  }


  public function setMapFn($value) {
    self::checkFn($value, self::MAP_DEFINITION, self::MAP_REGEX);
    $this->mapFn = $value;
  }


  public function getReduceFn() {
    return $this->reduceFn;
  }


  public function setReduceFn($value) {
    self::checkFn($value, self::REDUCE_DEFINITION, self::REDUCE_REGEX);
    $this->reduceFn = $value;
  }

}

?>