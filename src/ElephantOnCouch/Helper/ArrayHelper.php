<?php

//! @file ArrayHelper.php
//! @brief This file contains the ArrayHelper class.
//! @details
//! @author Filippo F. Fadda


//! @brief This is the helpers namespace.
namespace ElephantOnCouch\Helper;


//! @brief Helper with common array methods.
//! @nosubgrouping
class ArrayHelper {

  // @brief Checks if the array is associative.
  // @return bool
  static function isAssociative(array $array) {
    return (0 !== count(array_diff_key($array, array_keys(array_keys($array)))) || count($array) == 0);
  }


  // @brief Converts the array to an object.
  // @return object
  static function toObject(array $array) {
    return is_array($array) ? (object)array_map(__METHOD__, $array) : $array;
  }

}