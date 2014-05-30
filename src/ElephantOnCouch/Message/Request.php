<?php

/**
 * @file Request.php
 * @brief This file contains the Request class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Message;


use ElephantOnCouch\Helper;


/**
 * @brief This class represents an HTTP request. Since CouchDB is a RESTful server, we need make requests through an
 * HTTP client. That's the purpose of this class: emulate an HTTP request.
 * @nosubgrouping
 */
final class Request extends Message {

  /** @name Request Header Fields */
  //!@{

  /**
   * @brief Used to tell the server what media types are okay to send.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
   */
  const ACCEPT_HF = "Accept";

  /**
   * @brief Used to tell the server what charsets are okay to send.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.2
   */
  const ACCEPT_CHARSET_HF = "Accept-Charset";

  /**
   * @brief Used to tell the server what encodings are okay to send.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.3
   */
  const ACCEPT_ENCODING_HF = "Accept-Encoding";

  /**
   * @brief Used the server which languages are okay to send.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4
   */
  const ACCEPT_LANGUAGE_HF = "Accept-Language";

  /**
   * @brief Contains the data the client is supplying to the server to authenticate itself.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.8
   */
  const AUTHORIZATION_HF = "Authorization";

  /**
   * @brief Allows a client to list server behaviors that it requires for a request.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.20
   */
  const EXPECT_HF = "Expect";

  /**
   * @brief The From request-header field, if given, SHOULD contain an Internet e-mail address for the human user who
   * controls the requesting user agent.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.22
   */
  const FROM_HF = "From";

  /**
   * @brief Hostname and port of the server to which the request is being sent.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.23
   */
  const HOST_HF = "Host";

  /**
   * @brief The If-Match request-header field is used with a method to make it conditional. A client that has one or
   * more entities previously obtained from the resource can verify that one of those entities is current by including
   * a list of their associated entity tags in the If-Match header field.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.24
   */
  const IF_MATCH_HF = "If-Match";

  /**
   * @brief Restricts the request unless the resource has been modified since the specified date.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.25
   */
  const IF_MODIFIED_SINCE_HF = "If-Modified-Since";

  /**
   * @brief The If-None-Match request-header field is used with a method to make it conditional. A client that has one
   * or more entities previously obtained from the resource can verify that none of those entities is current by including
   * a list of their associated entity tags in the If-None-Match header field. The purpose of this feature is to allow
   * efficient updates of cached information with a minimum amount of transaction overhead.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.26
   */
  const IF_NONE_MATCH_HF = "If-None_Match";

  /**
   * @brief If a client has a partial copy of an entity in its cache, and wishes to have an up-to-date copy of the entire
   * entity in its cache, it could use the Range request-header with a conditional GET.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.27
   */
  const IF_RANGE_HF = "If-Range";

  /**
   * @brief Restricts the request unless the resource has not been modified since the specified date.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.28
   */
  const IF_UNMODIFIED_SINCE_HF = "If-Unmodified-Since-Header";

  /**
   * @brief The maximum number of times a request should be forwarded to another proxy or gateway on its way to the
   * origin server.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.31
   */
  const MAX_FORWARDS_HF = "Max-Forwards";

  /**
   * @brief Same as AUTHORIZATION_HF, but used when authenticating with a proxy.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.34
   */
  const PROXY_AUTHORIZATION_HF = "Proxy-Authorization";

  /**
   * @brief Request only part of an entity. Bytes are numbered from 0.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.35
   */
  const RANGE_HF = "Range";

  /**
   * @brief The Referer request-header field allows the client to specify, for the server's benefit, the address (URI)
   * of the resource from which the Request-URI was obtained.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.36
   */
  const REFERER_HF = "Referer";

  /**
   * @brief The TE request-header field indicates what extension transfer-codings it is willing to accept in the response
   * and whether or not it is willing to accept trailer fields in a chunked transfer-coding. Its value may consist of
   * the keyword "trailers" and/or a comma-separated list of extension transfer-coding names with optional accept
   * parameters.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.39
   */
  const TE_HF = "TE";

  /**
   * @brief Ask the server to upgrade to another protocol.;
   * @see http://en.wikipedia.org/wiki/Upgrade_HF
   */
  const UPGRADE_HF = "Upgrade";

  /**
   * @brief User agent info.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.43
   */
  const USER_AGENT_HF = "User-Agent";

  /**
   * @brief Used by clients to pass a token to the server.
   * @see http://en.wikipedia.org/wiki/HTTP_Cookie
   */
  const COOKIE_HF = "Cookie";

  /**
   * @brief Mainly used to identify Ajax requests. Most JavaScript frameworks send this header with value of XMLHttpRequest.
   */
  const X_REQUESTED_WITH_HF = "X-Requested-With";

  /**
   * @brief Requests a web application to disable their tracking of a user. Note that, as of yet, this is largely ignored
   * by web applications. It does however open the door to future legislation requiring web applications to comply with
   * a user's request to not be tracked. Mozilla implements the DNT header with a similar purpose.
   * @see http://en.wikipedia.org/wiki/X-Do-Not-Track
   */
  const X_DO_NOT_TRACK_HF = "X-Do-Not-Track";

  /**
   * @brief Requests a web application to disable their tracking of a user.
   * @details This is Mozilla's version of the X-Do-Not-Track header (since Firefox 4.0 Beta 11). Safari and IE9 also
   * have support for this header.[11] On March 7, 2011, a draft proposal was submitted to IETF.
   */
  const DNT_HF = "DNT";

  /**
   * @brief A de facto standard for identifying the originating IP address of a client connecting to a web server through
   * an HTTP proxy or load balancer.
   * @see http://en.wikipedia.org/wiki/X-Forwarded-For
   */
  const X_FORWARDED_FOR_HF = "X-Forwarded-For";

  const DESTINATION_HF = "Destination";  //!< This header field is not supported by HTTP 1.1. It's a special header field used by CouchDB.
  const X_COUCHDB_WWW_AUTHENTICATE_HF = "X-CouchDB-WWW-Authenticate";  //!< This header field is not supported by HTTP 1.1. It's a special method header field by CouchDB.
  const X_COUCHDB_FULL_COMMIT_HF = "X-Couch-Full-Commit";  //!< This header field is not supported by HTTP 1.1. It's a special header field used by CouchDB.

  //!@}

  // Stores the header fields supported by a Request.
  protected static $supportedHeaderFields = [
    self::ACCEPT_HF => NULL,
    self::ACCEPT_CHARSET_HF => NULL,
    self::ACCEPT_ENCODING_HF => NULL,
    self::ACCEPT_LANGUAGE_HF => NULL,
    self::AUTHORIZATION_HF => NULL,
    self::EXPECT_HF => NULL,
    self::FROM_HF => NULL,
    self::HOST_HF => NULL,
    self::IF_MATCH_HF => NULL,
    self::IF_MODIFIED_SINCE_HF => NULL,
    self::IF_NONE_MATCH_HF => NULL,
    self::IF_RANGE_HF => NULL,
    self::IF_UNMODIFIED_SINCE_HF => NULL,
    self::MAX_FORWARDS_HF => NULL,
    self::PROXY_AUTHORIZATION_HF => NULL,
    self::RANGE_HF => NULL,
    self::REFERER_HF => NULL,
    self::TE_HF => NULL,
    self::UPGRADE_HF => NULL,
    self::USER_AGENT_HF => NULL,
    self::COOKIE_HF => NULL,
    self::X_REQUESTED_WITH_HF => NULL,
    self::X_DO_NOT_TRACK_HF => NULL,
    self::DNT_HF => NULL,
    self::X_FORWARDED_FOR_HF => NULL,
    self::DESTINATION_HF => NULL,
    self::X_COUCHDB_WWW_AUTHENTICATE_HF => NULL,
    self::X_COUCHDB_FULL_COMMIT_HF => NULL,
  ];

  /** @name Request Methods */
  //!@{
  const GET_METHOD = "GET";
  const HEAD_METHOD = "HEAD";
  const POST_METHOD = "POST";
  const PUT_METHOD = "PUT";
  const DELETE_METHOD = "DELETE";
  const COPY_METHOD = "COPY"; //!< This method is not supported by HTTP 1.1. It's a special method used by CouchDB.
  //!@}

  // Stores the request methods supported by HTTP 1.1 protocol.
  private static $supportedMethods = [
    self::GET_METHOD => NULL,
    self::HEAD_METHOD => NULL,
    self::POST_METHOD => NULL,
    self::PUT_METHOD => NULL,
    self::DELETE_METHOD => NULL,
    self::COPY_METHOD => NULL
  ];

  // Used to know if the constructor has been already called.
  private static $initialized = FALSE;

  // Stores the request method.
  private $method;

  // Stores the request method.
  private $path;

  // Stores the request query's parameters.
  private $queryParams = [];


  /**
   * @brief Creates an instance of Request class.
   * @param[in] string $method The HTTP method for the request.
   * @param[in] string $path The absolute path of the request.
   * @param[in] string $queryParams (optional) Associative array of query parameters.
   * @param[in] string $headerFields (optional) Associative array of header fields.
   */
  public function __construct($method, $path, array $queryParams = NULL, array $headerFields = NULL) {
    parent::__construct();

    // We can avoid to call the following code every time a Request instance is created, testing a static property.
    // Because the static nature of self::$initialized, this code will be executed only one time, even multiple Request
    // instances are created.
    if (!self::$initialized) {
      self::$initialized = TRUE;
      self::$supportedHeaderFields += parent::$supportedHeaderFields;
    }

    $this->setMethod($method);
    $this->setPath($path);

    if (isset($queryParams))
      $this->setMultipleQueryParamsAtOnce($queryParams);

    if (isset($headerFields))
      $this->setMultipleHeaderFieldsAtOnce($headerFields);
  }


  /**
   * Returns a comprehensible representation of the HTTP Request to be used for debugging purpose.
   * @return string
   */
  public function __toString() {
    $request = [
      $this->getMethod()." ".$this->getPath().$this->getQueryString(),
      $this->getHeaderAsString(),
      $this->getBody()
    ];

    return implode(PHP_EOL.PHP_EOL, $request);
  }


  /**
   * @brief Retrieves the HTTP method used by the current request.
   * @return string
   */
  public function getMethod() {
    return $this->method;
  }


  /**
   * @brief Sets the HTTP method used by the current request.
   * @param[in] string $method The HTTP method for the request. You should use one of the public constants, like GET_METHOD.
   */
  public function setMethod($method) {
    if (array_key_exists($method, self::$supportedMethods))
      $this->method = $method;
    else
      throw new \UnexpectedValueException("$method method not supported. Use addCustomMethod() to add an unsupported method.");
  }


  /**
   * @brief Adds a non standard HTTP method.
   * @param[in] string $method The HTTP custom method.
   */
  public static function addCustomMethod($method) {
    if (array_key_exists($method, self::$supportedMethods))
      throw new \UnexpectedValueException("$method method is supported and already exists.");
    else
      self::$supportedMethods[] = $method;
  }


  /**
   * @brief Gets the absolute path for the current request.
   * @return string
   */
  public function getPath() {
    return $this->path;
  }


  /**
   * @brief Sets the request absolute path.
   * @param[in] string $path The absolute path of the request.
   */
  public function setPath($path) {
    if (is_string($path))
      $this->path = addslashes($path);
    else
      throw new \InvalidArgumentException("\$path must be a string.");
  }


  /**
   * @brief Used to set a query parameter. You can set many query parameters you want.
   * @param[in] string $name Parameter name.
   * @param[in] string $value Parameter value.
   */
  public function setQueryParam($name, $value) {
    if (preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $name))
      $this->queryParams[$name] = $value;
    else
      throw new \InvalidArgumentException("\$name must start with a letter or underscore, followed by any number of
          letters, numbers, or underscores.");
  }


  /**
   * @brief Used to set many parameters at once.
   * @param[in] array $params An associative array of parameters.
   */
  public function setMultipleQueryParamsAtOnce(array $params) {
    if (Helper\ArrayHelper::isAssociative($params))
      foreach ($params as $name => $value)
        $this->setQueryParam($name, $value);
    else
      throw new \InvalidArgumentException("\$params must be an associative array.");
  }


  /**
   * @brief Generates URL-encoded query string.
   * @return string
   */
  public function getQueryString() {
    if (empty($this->queryParams))
      return "";
    else
      // Encoding is based on RFC 3986.
      return "?".http_build_query($this->queryParams, NULL, "&", PHP_QUERY_RFC3986);
  }


  /**
   * @brief Parses the given query string and sets every single parameter contained into it.
   */
  public function setQueryString($value) {
    if (!empty($value)) {
      $query = ltrim($value, "?");

      $params = explode('&', $query);

      foreach ($params as $param) {
        @list($name, $value)  = explode('=', $param, 2);
        $this->setQueryParam($name, $value);
      }
    }
  }


  /**
   * @brief This helper forces request to use the Authorization Basic mode.
   * @param[in] string $userName User name.
   * @param[in] string $password Password.
   */
  public function setBasicAuth($userName, $password) {
    $this->setHeaderField(self::AUTHORIZATION_HF, "Basic ".base64_encode("$userName:$password"));
  }


  /**
   * @brief Returns a list of all supported methods.
   * @return associative array
   */
  public function getSupportedMethods() {
    return self::$supportedMethods;
  }

}