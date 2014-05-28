<?php

//! @file QueryResult.php
//! @brief This file contains the QueryResult class.
//! @details
//! @author Filippo F. Fadda


//! @brief The CouchDB's results namespace.
namespace ElephantOnCouch\Result;


//! @brief This class implements `IteratorAggregate`, `Countable`, and `ArrayAccess` and let you use the query's result
//! as an array.
//! @nosubgrouping
class QueryResult implements \IteratorAggregate, \Countable, \ArrayAccess {

  protected $result;


  public function __construct($result) {
    $this->result = $result;
  }


  //! @brief Gets the number of total rows in the view.
  //! @return integer Number of the view rows.
  public function getTotalRows() {
    return $this->result['total_rows'];
  }


  //! @brief Returns count or sum in case this is the result of a reduce function.
  //! @return integer The result of the reduce function.
  public function getReducedValue() {
    return empty($this->result['rows']) ? 0 : $this->result['rows'][0]['value'];
  }


  //! @brief Returns the result as a real array of rows to be used with `array_column()` or other functions operating on
  //! arrays.
  //! @returns array An array of rows.
  public function asArray()  {
    return $this->result['rows'];
  }


  //! @brief Returns an external iterator.
  //! @return An instance of `ArrayIterator`.
  public function getIterator() {
    return new \ArrayIterator($this->result['rows']);
  }


  //! @brief Returns the number of documents found.
  //! @return integer Number of documents.
  public function count() {
    return count($this->result['rows']);
  }


  //! @brief Whether or not an offset exists.
  //! @details This method is executed when using `isset()` or `empty()` on objects implementing ArrayAccess.
  //! @param[in] $offset An offset to check for.
  //! @return bool Returns `true` on success or `false` on failure.
  public function offsetExists($offset) {
    return isset($this->result['rows'][$offset]);
  }

  //! @brief Returns the value at specified offset.
  //! @details This method is executed when checking if offset is `empty()`.
  //! @param[in] $offset The offset to retrieve.
  //! @return Can return all value types.
  public function offsetGet($offset)  {
    return $this->result['rows'][$offset];
  }


  public function offsetSet($offset, $value) {
    throw new \BadMethodCallException("Result is immutable and cannot be changed.");
  }


  public function offsetUnset($offset) {
    throw new \BadMethodCallException("Result is immutable and cannot be changed.");
  }

}