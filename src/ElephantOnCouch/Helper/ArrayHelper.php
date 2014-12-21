<?php

/**
 * @file ArrayHelper.php
 * @brief This file contains the ArrayHelper class.
 * @details
 * @author Filippo F. Fadda
 */


//! This is the helpers namespace.
namespace ElephantOnCouch\Helper;


use ElephantOnCouch\Exception\JSONErrorException;


/**
 * @brief Helper with common array methods.
 * @nosubgrouping
 */
class ArrayHelper {


  /**
   * @brief Checks if the array is associative.
   * @param[in] array $array The array.
   * @return bool
   */
  public static function isAssociative(array $array) {
    return (0 !== count(array_diff_key($array, array_keys(array_keys($array)))) || count($array) == 0);
  }


  /*public static function convert
  array_walk_recursive($array, function(&$value, $key) {
    if (is_string($value)) {
      $value = iconv('windows-1252', 'utf-8', $value);
    }
  });*/


  /**
   * @brief Converts the array to an object.
   * @param[in] array $array The array to be converted.
   * @return object
   */
  public static function toObject(array $array) {
    return is_array($array) ? (object)array_map(__METHOD__, $array) : $array;
  }


  /**
   * @brief Converts the given JSON into an array.
   * @param[in] bool $assoc When `true`, returned objects will be converted into associative arrays.
   * @return array
   */
  public static function fromJson($json, $assoc) {
    $data = json_decode((string)$json, $assoc);

    if (is_null($data))
      switch (json_last_error()) {
        case JSON_ERROR_DEPTH:
          throw new JSONErrorException("Unable to parse the given JSON, the maximum stack depth has been exceeded.");
          break;
        case JSON_ERROR_STATE_MISMATCH:
          throw new JSONErrorException("Unable to parse the given JSON, invalid or malformed JSON.");
          break;
        case JSON_ERROR_CTRL_CHAR:
          throw new JSONErrorException("Unable to parse the given JSON, control character error, possibly incorrectly encoded.");
          break;
        case JSON_ERROR_SYNTAX:
          throw new JSONErrorException("Unable to parse the given JSON, syntax error.");
          break;
        case JSON_ERROR_UTF8:
          throw new JSONErrorException("Unable to parse the given JSON, malformed UTF-8 characters, possibly incorrectly encoded.");
          break;
      }

    return $data;
  }
}