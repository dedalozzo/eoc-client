<?php

/**
 * @file Response.php
 * @brief This file contains the Response class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Message;


/**
 * @brief After receiving and interpreting a request message, a server responds with an HTTP response message. This class
 * represents a server response.
 * @nosubgrouping
 */
final class Response extends Message {

  /** @name Response Header Fields */
  //!@{

  /**
   * @brief The Accept-Ranges response-header field allows the server to indicate its acceptance of range requests for
   * a resource.
   * @see http://www.w3.org/cols/rfc2616/rfc2616-sec14.html#sec14.5
   */
  const ACCEPT_RANGES_HF = "Accept-Ranges";

  /**
   * @brief The Age response-header field conveys the sender's estimate of the amount of time since the response (or its
   * revalidation) was generated at the origin server. A cached response is "fresh" if its age does not exceed its
   * freshness lifetime.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.6
   */
  const AGE_HF = "Age";

  /**
   * @brief In order to force the browser to show SaveAs dialog when clicking a hyperlink you have to include this
   * header field.
   */
  const CONTENT_DISPOSITION_HF = "Content-Disposition";

  /**
   * @brief An identifier for a specific version of a resource, often a message digest.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.19
   */
  const ETAG_HF = "ETag";

  /**
   * @brief Used to express a typed relationship with another resource, where the relation type is defined by RFC 5988.
   * @see http://tools.ietf.org/html/rfc5988
   */
  const LINK_HF = "Link";

  /**
   * @brief Used in redirection for completion of the request or identification of a new resource, or when a new resource
   * has been created.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.30
   */
  const LOCATION_HF = "Location";

  /**
   * @brief This header is supposed to set P3P policy, in the form of P3P:CP="your_compact_policy". However, P3P did not
   * take off, most browsers have never fully implemented it, a lot of websites set this header with fake policy text,
   * that was enough to fool browsers the existence of P3P policy and grant permissions for third party cookies.
   * @see http://en.wikipedia.org/wiki/P3P
   */
  const P3P_HF = "P3P";

  /**
   * @brief Request authentication to access the proxy.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.33
   */
  const PROXY_AUTHENTICATE_HF = "Proxy-Authenticate";

  /**
   * @brief Used in redirection, or when a new resource has been created. This refresh redirects after 5 seconds. This is
   * a proprietary, non-standard header extension introduced by Netscape and supported by most web browsers.
   * @see http://en.wikipedia.org/wiki/HTTP_refresh
   */
  const REFRESH_HF = "Refresh";

  /**
   * @brief If an entity is temporarily unavailable, this instructs the client to try again after a specified period of time (seconds).
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.37
   */
  const RETRY_AFTER_HF = "Retry-After";

  /**
   * @brief This response-header specifies the server name.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.38
   */
  const SERVER_HF = "Server";

  /**
   * @brief Sets an HTTP Cookie.
   * @see http://en.wikipedia.org/wiki/HTTP_cookie
   */
  const SET_COOKIE_HF = "Set-Cookie";

  /**
   * @brief A HSTS Policy informing the HTTP client how long to cache the HTTPS only policy and whether this applies to
   * subdomains.
   * @see http://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security
   */
  const STRICT_TRANSPORT_SECURITY_HF = "Strict-Transport-Security";

  /**
   * @brief Tells downstream proxies how to match future request headers to decide whether the cached response can be
   * used rather than requesting a fresh one from the origin server.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.44
   */
  const VARY_HF = "Vary";

  /**
   * @brief Indicates the authentication scheme that should be used to access the requested entity.
   * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.47
   */
  const WWW_AUTHENTICATE_HF = "WWW-Authenticate";

  /**
   * @brief Clickjacking protection: "deny" - no rendering within a frame, "sameorigin" - no rendering if origin mismatch.
   * @see http://en.wikipedia.org/wiki/Clickjacking
   */
  const X_FRAME_OPTIONS_HF = "X-Frame-Options";

  /**
   * @brief Cross-site scripting (XSS) filter.
   * @see http://en.wikipedia.org/wiki/Cross-site_scripting
   */
  const X_XSS_PROTECTION_HF = "X-XSS-Protection";

  /**
   * @brief The only defined value, "nosniff", prevents Internet Explorer from MIME-sniffing a response away from the
   * declared content-type.
   */
  const X_CONTENT_TYPE_OPTIONS_HF = "X-Content-Type-Options";

  /**
   * @brief A de facto standard for identifying the originating protocol of an HTTP request, since a reverse proxy (load
   * balancer) may communicate with a web server using HTTP even if the request to the reverse proxy is HTTPS.
   */
  const X_FORWARDED_PROTO_HF = "X-Forwarded-Proto";

  /**
   * @brief Specifies the technology (e.g. ASP.NET, PHP, JBoss) supporting the web application (version details are often
   * in X-Runtime, X-Version, or X-AspNet-Version)
   */
  const X_POWERED_BY_HF = "X-Powered-By";

  /**
   * @brief Recommends the preferred rendering engine (often a backward-compatibility mode) to use to display the content.
   * Also used to activate Chrome Frame in Internet Explorer.
   * @see http://en.wikipedia.org/wiki/Chrome_Frame
   */
  const X_UA_COMPATIBLE_HF = "X-UA-Compatible";

  //!@}

  // Stores the header fields supported by a Response.
  protected static $supportedHeaderFields = [
    self::ACCEPT_RANGES_HF => NULL,
    self::AGE_HF => NULL,
    self::CONTENT_DISPOSITION_HF => NULL,
    self::ETAG_HF => NULL,
    self::LINK_HF => NULL,
    self::LOCATION_HF => NULL,
    self::P3P_HF => NULL,
    self::PROXY_AUTHENTICATE_HF => NULL,
    self::REFRESH_HF => NULL,
    self::RETRY_AFTER_HF => NULL,
    self::SERVER_HF => NULL,
    self::SET_COOKIE_HF => NULL,
    self::STRICT_TRANSPORT_SECURITY_HF => NULL,
    self::VARY_HF => NULL,
    self::WWW_AUTHENTICATE_HF => NULL,
    self::X_FRAME_OPTIONS_HF => NULL,
    self::X_XSS_PROTECTION_HF => NULL,
    self::X_CONTENT_TYPE_OPTIONS_HF => NULL,
    self::X_FORWARDED_PROTO_HF => NULL,
    self::X_POWERED_BY_HF => NULL,
    self::X_UA_COMPATIBLE_HF => NULL
  ];

  /** @name Informational Status Codes */
  //!@{

  /**
   * @brief Indicates that an initial part of the request was received and the client should continue.
   * @details After sending this, the server must respond after receiving the body request.
   */
  const CONTINUE_SC = 100;

  //!@}

  /** @name Success Status Codes
  //!@{

  /**
   * @brief Request is OK, entity body contains requested resource.
   */ 
  const OK_SC = 200;

  /**
   * @brief For requests that create server objects (e.g., PUT).
   * @details The entity body of the response should contain the various URLs for referencing the created resource,
   * with the Location header containing the most specific reference.
   */
  const CREATED_SC = 201;

  /**
   * @brief The request was accepted, but the server has not yet performed any action with it.
   * @details There are no guarantees that the server will complete the request; this just means that the request
   * looked valid when accepted.
   */
  const ACCEPTED_SC = 202;

  /**
   * @brief The response message contains headers and a status line, but no entity body.
   * @details Primarily used to update browsers without having them move to a new document.
   */
  const NO_CONTENT_SC = 204;

  /**
   * @brief A partial or range request was successful.
   * @details A 206 response must include a Content-Range, Date, and either ETag or Content-Location header.
   */
  const PARTIAL_CONTENT_SC = 206;

  //!@}

  /** @name Redirection Status Codes */
  //!@{

  /**
   * @brief Used when the requested URL has been moved.
   * @details The response should contain in the Location header the URL where the resource now resides.
   */
  const MOVED_PERMANENTLY_SC = 301;

  /**
   * @brief Like the 301 status code.
   * @details However, the client should use the URL given in the Location header to locate the resource temporarily.
   * Future requests should use the old URL.
   */
  const FOUND_SC = 302;

  /**
   * @brief Clients can make their requests conditional by the request headers they include.
   * @details If a client makes a conditional request, such as a GET if the resource has not been changed recently,
   * this code is used to indicate that the resource has not changed. Responses with this status code should not
   * contain an entity body.
   */
  const NOT_MODIFIED_SC = 304;

  //!@}

  /** @name Client Error Status Codes */
  //!@{

  /**
   * @brief Used to tell the client that it has sent a malformed request.
   */
  const BAD_REQUEST_SC = 400;

  /**
   * @brief Returned along with appropriate headers that ask the client to authenticate itself before it can gain access
   * to the resource.
   */
  const UNAUTHORIZED_SC = 401;

  /**
   * @brief Used to indicate that the request was refused by the server.
   * @details If the server wants to indicate why the request was denied, it can include an entity body describing the
   * reason. However, this code usually is used when the server does not want to reveal the reason for the refusal.
   */
  const FORBIDDEN_SC = 403;

  /**
   * @brief Used to indicate that the server cannot find the requested URL.
   * @details Often, an entity is included for the client application to display to the user.
   */
  const NOT_FOUND_SC = 404;

  /**
   * @brief Used when a request is made with a method that is not supported for the requested URL.
   * @details The Allow header should be included in the response to tell the client what methods are allowed on the
   * requested resource.
   */
  const METHOD_NOT_ALLOWED_SC = 405;

  /**
   * @brief Clients can specify parameters about what types of entities they are willing to accept.
   * @details This code is used when the server has no resource matching the URL that is acceptable for the client.
   * Often, servers include headers that allow the client to figure out why the request could not be satisfied.
   */
  const NOT_ACCEPTABLE_SC = 406;

  /**
   * @brief Used to indicate some conflict that the request may be causing on a resource.
   * @details Servers might send this code when they fear that a request could cause a conflict. The response should
   * contain a body describing the conflict.
   */
  const CONFLICT_SC = 409;

  /**
   * @brief Used if a client makes a conditional request and one of the conditions fails.
   * @details Conditional requests occur when a client includes an unexpected header.
   */
  const PRECONDITION_FAILED_SC = 412;

  /**
   * @brief Used when a client sends an entity body that is larger than the server can or wants to process.
   */
  const REQUEST_ENTITY_TOO_LARGE_SC = 413;

  /**
   * @brief Used when a client sends an entity of a content type that the server does not understand or support.
   */
  const UNSUPPORTED_MEDIA_TYPE_SC = 415;

  /**
   * @brief Used when the request message requested a range of a given resource and that range either was invalid or
   * could not be met.
   */
  const REQUESTED_RANGE_NOT_SATISFIABLE_SC = 416;

  /**
   * @brief Used when the request contained an expectation in the request header that the server could not satisfy.
   */
  const EXPECTATION_FAILED_SC = 417;

  //!@}
  
  /** @name Server Error Status Codes */
  //!@{

  /**
   * @brief Used when the server encounters an error that prevents it from servicing the request.
   */
  const INTERNAL_SERVER_ERROR_SC = 500;

  //!@}

  // Array of HTTP Status Codes
  private static $supportedStatusCodes = [
      // Informational Status Codes
      self::CONTINUE_SC => "Continue",
      // Success Status Codes
      self::OK_SC => "OK",
      self::CREATED_SC => "Created",
      self::ACCEPTED_SC => "Accepted",
      self::NO_CONTENT_SC => "No Content",
      self::PARTIAL_CONTENT_SC => "Partial Content",
      // Redirection Status Codes
      self::MOVED_PERMANENTLY_SC => "Moved Permanently",
      self::FOUND_SC => "Found",
      self::NOT_MODIFIED_SC => "Not Modified",
      // Client Error Status Codes
      self::BAD_REQUEST_SC => "Bad Request",
      self::UNAUTHORIZED_SC => "Unauthorized",
      self::FORBIDDEN_SC => "Forbidden",
      self::NOT_FOUND_SC => "Not Found",
      self::METHOD_NOT_ALLOWED_SC => "Method Not Allowed",
      self::NOT_ACCEPTABLE_SC => "Not Acceptable",
      self::CONFLICT_SC => "Conflict",
      self::PRECONDITION_FAILED_SC => "Precondition Failed",
      self::REQUEST_ENTITY_TOO_LARGE_SC => "Request Entity Too Large",
      self::UNSUPPORTED_MEDIA_TYPE_SC => "Unsupported Media Type",
      self::REQUESTED_RANGE_NOT_SATISFIABLE_SC => "Requested Range Not Satisfiable",
      self::EXPECTATION_FAILED_SC => "Expectation Failed",
      // Server Error Status Codes
      self::INTERNAL_SERVER_ERROR_SC => "Internal Server Error",
  ];

  private $statusCode;

  // Used to know if the constructor has been already called.
  private static $initialized = FALSE;


  /**
   * @brief Creates a new Response object.
   * @param[in] string $message The complete Response string.
   */
  public function __construct($message) {
    parent::__construct();

    // We can avoid to call the following code every time a Response instance is created, testing a static property.
    // Because the static nature of self::$initialized, this code will be executed only one time, even multiple Response
    // instances are created.
    if (!self::$initialized) {
      self::$initialized = TRUE;
      self::$supportedHeaderFields += parent::$supportedHeaderFields;
    }

    $this->parseStatusCodeAndHeader($message);
  }


  /**
   * Returns a comprehensible representation of the HTTP Response to be used for debugging purpose.
   * @return string
   */
  public function __toString() {
    $response = [
      $this->getStatusCode()." ".$this->getSupportedStatusCodes()[$this->getStatusCode()],
      $this->getHeaderAsString(),
      $this->getBody()
    ];

    return implode(PHP_EOL.PHP_EOL, $response);
  }


  protected function parseStatusCode($rawMessage) {
    $matches = [];
    if (preg_match('%HTTP/1\.[0-1] (\d\d\d) %', $rawMessage, $matches))
      $this->statusCode = $matches[1];
    else
      throw new \UnexpectedValueException("HTTP Status Code undefined.");

    if (!array_key_exists($this->statusCode, self::$supportedStatusCodes))
      throw new \UnexpectedValueException("HTTP Status Code unknown.");
  }


  protected function parseHeaderFields($rawHeader) {
    $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $rawHeader));

    foreach ($fields as $field) {
      if (preg_match('/([^:]+): (.+)/m', $field, $matches)) {
        // With the advent of PHP 5.5, the /e modifier is deprecated, so we use preg_replace_callback().
        $matches[1] = preg_replace_callback('/(?<=^|[\x09\x20\x2D])./',
            function($matches) {
              return strtoupper($matches[0]);
            },
            strtolower(trim($matches[1])));

        if (isset($this->header[$matches[1]]))
          $this->header[$matches[1]] = array($this->header[$matches[1]], $matches[2]);
        else
          $this->header[$matches[1]] = trim($matches[2]);
      }
    }
  }


  protected function parseStatusCodeAndHeader($rawMessage) {
    if (!is_string($rawMessage))
      throw new \InvalidArgumentException("\$rawMessage must be a string.");

    if (empty($rawMessage))
      throw new \UnexpectedValueException("\$rawMessage is null.");

    $this->parseStatusCode($rawMessage);

    // In case server sends a "100 Continue" response, we must parse the message twice. This happens when a client uses
    // the "Expect: 100-continue" header field.
    // @see http://www.jmarshall.com/easy/http/#http1.1s5
    if ($this->statusCode == self::CONTINUE_SC) {
      $rawMessage = preg_split('/\r\n\r\n/', $rawMessage, 2)[1];
      $this->parseStatusCodeAndHeader($rawMessage);
    }

    $rawMessage = preg_split('/\r\n\r\n/', $rawMessage, 2);

    if (empty($rawMessage))
      throw new \RuntimeException("The server didn't return a valid Response for the Request.");

    // $rawMessage[0] contains header fields.
    $this->parseHeaderFields($rawMessage[0]);

    // $rawMessage[1] contains the entity-body.
    $this->body = $rawMessage[1];
  }


  /**
   * @brief Returns the HTTP Status Code for the current response.
   * @return string
   */
  public function getStatusCode() {
    return $this->statusCode;
  }


  /**
   * @brief Sets the Response status code.
   * @param[in] int $value The status code.
   */
  public function setStatusCode($value) {
    if (array_key_exists($value, self::$supportedStatusCodes)) {
      $this->statusCode = $value;
    }
    else
      throw new \UnexpectedValueException("Status Code $value is not supported.");
  }


  /**
   * @brief Returns a list of all supported status codes.
   * @return associative array
   */
  public function getSupportedStatusCodes() {
    return self::$supportedStatusCodes;
  }


  /**
   * @brief Adds a non standard HTTP Status Code.
   * @param[in] string $code The Status Code.
   * @param[in] string $description A description for the Status Code.
   */
  public static function addCustomStatusCode($code, $description) {
    if (in_array($code, self::$supportedStatusCodes))
      throw new \UnexpectedValueException("Status Code $code is supported and already exists.");
    elseif (is_int($code) and $code > 0)
      self::$supportedStatusCodes[$code] = $description;
    else
      throw new \InvalidArgumentException("\$code must be a positive integer.");
  }

}