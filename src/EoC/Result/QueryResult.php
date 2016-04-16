<?php

/**
 * @file QueryResult.php
 * @brief This file contains the QueryResult class.
 * @details
 * @author Filippo F. Fadda
 */


//! The CouchDB's results namespace.
namespace EoC\Result;


/**
 * @brief This class implements `IteratorAggregate`, `Countable`, and `ArrayAccess` and let you use the query's result
 * as an array.
 * @nosubgrouping
 */
class QueryResult implements \IteratorAggregate, \Countable, \ArrayAccess {

  protected $result;


  /**
   * @brief Creates an instance of the class
   * @param[in] array $result The result of a CouchDB's query converted from JSON to array.
   */
  public function __construct($result) {
    $this->result = &$result;
  }


  /**
   * @brief Gets the number of total rows in the view.
   * @retval integer Number of the view rows.
   */
  public function getTotalRows() {
    return $this->result['total_rows'];
  }


  /**
   * @brief Returns count or sum in case this is the result of a reduce function.
   * @retval integer The result of the reduce function.
   */
  public function getReducedValue() {
    return empty($this->result['rows']) ? 0 : $this->result['rows'][0]['value'];
  }


  /**
   * @brief Returns the result as a real array of rows to be used with `array_column()` or other functions operating on
   * arrays.
   * @retval array An array of rows.
   */
  public function asArray()  {
    return $this->result['rows'];
  }


  /**
   * @brief Returns `true` in case there aren't rows, `false` otherwise.
   * @details Since the PHP core developers are noobs, `empty()` cannot be used on any class that implements ArrayAccess.
   * @attention This method must be used in place of `empty()`.
   * @retval bool
   */
  public function isEmpty() {
    return empty($this->result['rows']) ? TRUE : FALSE;
  }


  /**
   * @brief Returns an external iterator.
   * @retval [ArrayIterator](http://php.net/manual/en/class.arrayiterator.php).
   */
  public function getIterator() {
    return new \ArrayIterator($this->result['rows']);
  }


  /**
   * @brief Returns the number of documents found.
   * @retval integer Number of documents.
   */
  public function count() {
    return count($this->result['rows']);
  }


  /**
   * @brief Whether or not an offset exists.
   * @details This method is executed when using `isset()` or `empty()` on objects implementing ArrayAccess.
   * @param[in] integer $offset An offset to check for.
   * @retval bool Returns `true` on success or `false` on failure.
   */
  public function offsetExists($offset) {
    return isset($this->result['rows'][$offset]);
  }


  /**
   * @brief Returns the value at specified offset.
   * @details This method is executed when checking if offset is `empty()`.
   * @param[in] integer $offset The offset to retrieve.
   * @retval mixed Can return all value types.
   */
  public function offsetGet($offset)  {
    return $this->result['rows'][$offset];
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