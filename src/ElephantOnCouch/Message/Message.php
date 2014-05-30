<?php

/**
 * @file Message.php
 * @brief This file contains the Message class.
 * @details
 * @author Filippo F. Fadda
 */

//! This is the HTTP namespace.
namespace ElephantOnCouch\Message;


use ElephantOnCouch\Adapter;
use ElephantOnCouch\Helper;


/**
 * @brief An HTTP Message can either be a Request or a Response. This class represents a generic message with common
 * properties and methods.
 * @nosubgrouping
 */
abstract class Message {

  //! CR+LF (0x0D 0x0A). A Carriage Return followed by a Line Feed. We don't use PHP_EOL because HTTP wants CR+LF.
  const CRLF = "\r\n";


  /** @name General Header Fields */
  //!@{

  /**
   * @brief Used to specify directives that MUST be obeyed by all caching mechanisms along the request/response chain.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
   */
  const CACHE_CONTROL_HF = "Cache-Control";

  /**
   * @brief HTTP/1.1 defines the "close" connection option for the sender to signal that the connection will be closed
   * after completion of the response. HTTP/1.1 applications that do not support persistent connections MUST include
   * the "close" connection option in every message.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.10
   */
  const CONNECTION_HF = "Connection";

  /**
   * @brief Provides a date and time stamp telling when the message was created.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.18
   */
  const DATE_HF = "Date";

  /**
   * @brief The Pragma general-header field is used to include implementation- specific directives that might apply to
   * any recipient along the request/response chain.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.32
   */
  const PRAGMA_HF = "Pragma";

  /**
   * @brief Lists the set of headers that are in the trailer of a message encoded with the chunked transfer encoding.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.40
   */
  const TRAILER_HF = "Trailer";

  /**
   * @brief Tells the receiver what encoding was performed on the message in order for it to be transported safely.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.41
   */
  const TRANSFER_ENCODING_HF = "Transfer-Encoding";

  /**
   * @brief Gives a new version or protocol that the sender would like to "upgrade" to using.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.42
   */
  const UPGRADE_HF = "Upgrade";

  /**
   * @brief Shows what intermediaries (proxies, gateways) the message has gone through.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.45
   */
  const VIA_HF = "Via";

  /**
   * @brief The Warning general-header field is used to carry additional information about the status or transformation
   * of a message which might not be reflected in the message. This information is typically used to warn about a possible
   * lack of semantic transparency from caching operations or transformations applied to the entity body of the message.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.46
   */
  const WARNING_HF = "Warning";

  //!@}

  /** @name Entity Header Fields */
  //!@{

  /**
   * @brief The purpose of this field is strictly to inform the recipient of valid methods associated with the resource.
   * To be used for a 405 Method not allowed.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
   */
  const ALLOW_HF = "Allow";

  /**
   * @brief The Content-Encoding entity-header field is used as a modifier to the media-type. When present, its value
   * indicates what additional content codings have been applied to the entity-body, and thus what decoding mechanisms
   * must be applied in order to obtain the media-type referenced by the Content-Type header field. Content-Encoding is
   * primarily used to allow a document to be compressed without losing the identity of its underlying media type.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.11
   */
  const CONTENT_ENCODING_HF = "Content-Encoding";

  /**
   * @brief The natural language that is best used to understand the body.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.12
   */
  const CONTENT_LANGUAGE_HF = "Content-Language";

  /**
   * @brief The length or size of the body.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.13
   */
  const CONTENT_LENGTH_HF = "Content-Length";

  /**
   * @brief Where the resource actually is located.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.14
   */
  const CONTENT_LOCATION_HF = "Content-Location";

  /**
   * @brief An MD5 checksum of the body.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.15
   */
  const CONTENT_MD5_HF = "Content-MD5";

  /**
   * @brief The range of bytes that this entity represents from the entire resource.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.16
   */
  const CONTENT_RANGE_HF = "Content-Range";

  /**
   * @brief The type of object that this body is.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.17
   */
  const CONTENT_TYPE_HF = "Content-Type";

  /**
   * @brief The Expires entity-header field gives the date/time after which the response is considered stale. A stale
   * cache entry may not normally be returned by a cache (either a proxy cache or a user agent cache) unless it is first
   * validated with the origin server (or with an intermediate cache that has a fresh copy of the entity).
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.21
   */
  const EXPIRES_HF = "Expires";

  /**
   * @brief The last modified date for the requested object.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.29
   */
  const LAST_MODIFIED_HF = "Last-Modified";

  //!@}

  // Stores the header fields supported by a Message.
  protected static $supportedHeaderFields = [
    self::CACHE_CONTROL_HF => NULL,
    self::CONNECTION_HF => NULL,
    self::DATE_HF => NULL,
    self::PRAGMA_HF => NULL,
    self::TRAILER_HF => NULL,
    self::TRANSFER_ENCODING_HF => NULL,
    self::UPGRADE_HF => NULL,
    self::VIA_HF => NULL,
    self::WARNING_HF => NULL,
    self::ALLOW_HF => NULL,
    self::CONTENT_ENCODING_HF => NULL,
    self::CONTENT_LANGUAGE_HF => NULL,
    self::CONTENT_LENGTH_HF => NULL,
    self::CONTENT_LOCATION_HF => NULL,
    self::CONTENT_MD5_HF => NULL,
    self::CONTENT_RANGE_HF => NULL,
    self::CONTENT_TYPE_HF => NULL,
    self::EXPIRES_HF => NULL,
    self::LAST_MODIFIED_HF => NULL
  ];

  // Stores the message header fields.
  protected $header = [];

  // Stores the entity body.
  protected $body = "";


  /**
   * @brief Creates a Message object.
   * @details I wanted declare it abstract, but since PHP sucks, I can't define a constructor, in a derived class, with
   * a different number of parameters, not when the parent's class constructor is abstract.
   */
  public function __construct() {
  }


  /**
   * @brief Returns an array of well-formed header fields. Ex: <c>Content-Type: application/json</c>.
   * @return array of strings
   */
  public function getHeaderAsArray() {
    $wellformedHeader = [];
    foreach ($this->header as $name => $value)
      $wellformedHeader[] = $name.": ".$value;

    return $wellformedHeader;
  }


  /**
   * @brief Returns the header string.
   * @return string
   */
  public function getHeaderAsString() {
    return implode(self::CRLF, $this->getHeaderAsArray());
  }


  /**
   * @brief Returns `TRUE` in case exist the specified header field or `FALSE` in case it doesn't exist.
   * @param[in] string $name Header field name.
   * @return boolean
   */
  public function hasHeaderField($name) {
    return (array_key_exists($name, $this->header)) ? TRUE : FALSE;
  }


  /**
   * @brief Returns the value for the header identified by the specified name or `FALSE` in case it doesn't exist.
   * @param[in] string $name Header field name.
   * @return string
   */
  public function getHeaderFieldValue($name) {
    if ($this->hasHeaderField($name))
      return $this->header[$name];
    else
      return FALSE;
  }


  /**
   * @brief Adds to the headers associative array, the provided header's name and value.
   * @param[in] string $name The header field name.
   * @param[in] string $value The header field value.
   */
  public function setHeaderField($name, $value) {
    if (array_key_exists($name, static::$supportedHeaderFields))
      $this->header[$name] = $value;
    else
      throw new \Exception("$name header field is not supported.");
  }


  /**
   * @brief Removes the header field from the headers associative array.
   * @param[in] string $name The header field name.
   */
  public function removeHeaderField($name) {
    if (array_key_exists($name, static::$supportedHeaderFields))
      unset($this->header[$name]);
  }


  /**
   * @brief Used to set many header fields at once.
   * @param[in] array $headerFields An associative array of header fields.
   */
  public function setMultipleHeaderFieldsAtOnce(array $headerFields) {
    if (Helper\ArrayHelper::isAssociative($headerFields))
      foreach ($headerFields as $name => $value)
        $this->setHeaderField($name, $value);
    else
      throw new \Exception("\$headerFields must be an associative array.");
  }


  /**
   * @brief Adds a non standard HTTP header.
   * @param[in] string $name Header field name.
   */
  public static function addCustomHeaderField($name) {
    if (array_key_exists($name, static::$supportedHeaderFields))
      throw new \Exception("$name header field is supported but already exists.");
    else
      static::$supportedHeaderFields[] = $name;
  }


  /**
   * @brief Returns a list of all supported header fields.
   * @return associative array
   */
  public function getSupportedHeaderFields() {
    return static::$supportedHeaderFields;
  }


  /**
   * @brief Returns the Message entity-body as raw string. The string can be in JSON, XML or a proprietary format,
   * depends by the server implementation.
   * @return string
   */
  public function getBody() {
    return $this->body;
  }


  /**
   * @brief Returns the Message entity-body JSON as an array.
   * @param[in] bool $assoc When `true`, returned objects will be converted into associative arrays.
   * @return associative array
   */
  public function getBodyAsArray($assoc = TRUE) {
    return Helper\ArrayHelper::fromJson($this->body, $assoc);
  }


  /**
   * @brief Returns the Message entity-body JSON as an object.
   * @return object
   */
  public function getBodyAsObject() {
    return Helper\ArrayHelper::toObject($this->getBodyAsArray(FALSE));
  }


  /**
   * @brief Sets the Message entity-body.
   * @param[in] string $body
   */
  public function setBody($body) {
    $this->body = (string)$body;
  }


  /**
   * @brief Checks if the Message has a body.
   * @return boolean
   */
  public function hasBody() {
    return (empty($this->body)) ? FALSE : TRUE;
  }


  /**
   * @brief Returns the body length.
   * @return integer
   */
  public function getBodyLength() {
    return strlen($this->body);
  }

}