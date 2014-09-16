<?php

/**
 * @file QueryResult.php
 * @brief This file contains the QueryResult class.
 * @details
 * @author Filippo F. Fadda
 */


//! The CouchDB's results namespace.
namespace ElephantOnCouch\Result;


/**
 * @brief This class implements `IteratorAggregate`, `Countable`, and `ArrayAccess` and let you use the query's result
 * as an array.
 * @nosubgrouping
 */
class QueryResult implements \IteratorAggregate, \Countable, \ArrayAccess, \SeekableIterator {

  private $result;
  private $rows;
  private $position;


  /**
   * @brief Creates an instance of the class
   * @param[in] array $result The result of a CouchDB's query converted from JSON to array.
   */
  public function __construct($result) {
    $this->result = &$result;
    $this->rows = &$this->result['rows'];
  }


  /**
   * @brief Gets the number of total rows in the view.
   * @return integer Number of the view rows.
   */
  public function getTotalRows() {
    return $this->result['total_rows'];
  }


  /**
   * @brief Returns count or sum in case this is the result of a reduce function.
   * @return integer The result of the reduce function.
   */
  public function getReducedValue() {
    return empty($this->rows) ? 0 : $this->rows[0]['value'];
  }


  /**
   * @brief Returns the result as a real array of rows to be used with `array_column()` or other functions operating on
   * arrays.
   * @returns array An array of rows.
   */
  public function asArray()  {
    return $this->rows;
  }


  /**
   * @brief Returns `true` in case there aren't rows, `false` otherwise.
   * @details Since the PHP core developers are noobs, `empty()` cannot be used on any class that implements ArrayAccess.
   * This method must be used in place of `empty()`.
   * @return bool
   */
  public function isEmpty() {
    return empty($this->rows) ? TRUE : FALSE;
  }


  /**
   * @brief Returns an external iterator.
   * @return \ArrayIterator.
   */
  public function getIterator() {
    return new \ArrayIterator($this->rows);
  }


  /**
   * @brief Returns the number of documents found.
   * @return integer Number of documents.
   */
  public function count() {
    return count($this->rows);
  }


  /**
   * @brief Whether or not an offset exists.
   * @details This method is executed when using `isset()` or `empty()` on objects implementing ArrayAccess.
   * @param[in] integer $offset An offset to check for.
   * @return bool Returns `true` on success or `false` on failure.
   */
  public function offsetExists($offset) {
    return isset($this->rows[$offset]);
  }


  /**
   * @brief Returns the value at specified offset.
   * @details This method is executed when checking if offset is `empty()`.
   * @param[in] integer $offset The offset to retrieve.
   * @return mixed Can return all value types.
   */
  public function offsetGet($offset)  {
    return $this->rows[$offset];
  }


  /**
   * @brief Seeks to a given position in the iterator.
   * @param[in] int $position The position to seek for.
   */
  public function seek($position) {
    if (!isset($this->rows[$position]))
      throw new \OutOfBoundsException("Invalid seek position ($position).");

    $this->position = $position;
  }


  /**
   * @brief Returns the current element.
   * @return mixed Can return all value types.
   */
  public function current() {
    return $this->rows[$this->position];
  }


  /**
   * @brief Returns the key of the current element.
   * @return scalar Scalar on success, or `null` on failure.
   */
  public function key() {
    return $this->position;
  }


  /**
   * @brief Moves the current position to the next element.
   */
  public function next() {
    ++$this->position;
  }


  /**
   * @brief Rewinds back to the first element of the Iterator.
   */
  public function rewind() {
    $this->position = 0;
  }


  /**
   * @brief Checks if current position is valid.
   * @return bool Returns `true` on success or `false` on failure.
   */
  public function valid() {
    return isset($this->rows[$this->position]);
  }


  //! @cond HIDDEN_SYMBOLS

  public function offsetSet($offset, $value) {
    throw new \BadMethodCallException("Result is immutable and cannot be changed.");
  }


  public function offsetUnset($offset) {
    throw new \BadMethodCallException("Result is immutable and cannot be changed.");
  }

  //! @endcond

}