<?php

//! @file Couch.php
//! @brief This file contains the Couch class.
//! @details
//! @author Filippo F. Fadda


//! @brief This is the main ElephantOnCouch library namespace.
namespace ElephantOnCouch;


use ElephantOnCouch\Exception\ServerErrorException;
use ElephantOnCouch\Message\Message;
use ElephantOnCouch\Message\Request;
use ElephantOnCouch\Message\Response;


//! @brief The CouchDB's client. You need an instance of this class to interact with CouchDB.
//! @details This client is using HTTP/1.1 version. Encoding is made according RFC 3986, using rawurlencode().
//! @nosubgrouping
//! @todo Check ISO-8859-1 because CouchDB use it, in particular utf8_encode().
//! @todo Add Proxy support.
//! @todo Add SSL support.
//! @todo Add Post File support.
//! @todo Add Memcached support. Remember to use Memcached extension, not memcache.
//! @todo Implement getDbChanges().
//! @todo Implement getSecurityObj().
//! @todo Implement setSecurityObj().
//! @todo Implement cancelReplication().
//! @todo Implement getReplicator().
//! @todo Implement purgeDocs().
//! @todo Implement performBulkOperations().
//! @todo Implement showDoc().
//! @todo Implement listDocs().
//! @todo Implement callUpdateDocFunc().
//! @todo Implement createAdminUser().
//! @todo Implement getAccessToken().
//! @todo Implement getAuthorize().
//! @todo Implement setAuthorize().
//! @todo Implement requestToken().
final class Couch {

  //! @name User Agent
  //! @brief User agent's information.
  // @{
  const USER_AGENT_NAME = "ElephantOnCouch";
  const USER_AGENT_VERSION = "0.2.1";
  //@}

  //! Default server.
  const DEFAULT_SERVER = "127.0.0.1:5984";

  //! HTTP protocol version.
  const HTTP_VERSION = "HTTP/1.1";

  //! CR+LF (0x0D 0x0A). A Carriage Return followed by a Line Feed. We don't use PHP_EOL because HTTP wants CR+LF.
  const CRLF = "\r\n";

  const BUFFER_LENGTH = 8192;

  //! Maximum period to wait before the response is sent.
  const DEFAULT_TIMEOUT = 60000;

  const SOCKET_TRANSPORT = 0;
  const CURL_TRANSPORT = 1;

  const SCHEME_HOST_PORT_URI = '/^
	        (?P<scheme>tcp:\/\/|ssl:\/\/|tls:\/\/)?          # Scheme
	        # Authority
	        (?P<host>[a-z0-9\-._~%]+                         # Named host
	        |     \[[a-f0-9:.]+\]                            # IPv6 host
	        |     \[v[a-f0-9][a-z0-9\-._~%!$&\'()*+,;=:]+\]) # IPvFuture host
	        (?P<port>:[0-9]+)?                               # Port
	        $/ix';

  //! Default CouchDB revisions limit number.
  const REVS_LIMIT = 1000;

  //! @name Document Paths
  // @{
  const STD_DOC_PATH = ""; //!< Path for standard documents.
  const LOCAL_DOC_PATH = "_local/"; //!< Path for local documents.
  const DESIGN_DOC_PATH = "_design/"; //!< Path for design documents.
  //@}

  // Stores the document paths supported by CouchDB.
  private static $supportedDocPaths = [
    self::STD_DOC_PATH => NULL,
    self::LOCAL_DOC_PATH => NULL,
    self::DESIGN_DOC_PATH => NULL
  ];

  // Stores the transport mode. This library can use cURL or sockets.
  private static $transport = self::SOCKET_TRANSPORT;

  private $scheme;
  private $host;
  private $port;

  private $userName;
  private $password;

  // Current selected rawencoded database name.
  private $dbName;

  // URI specifying address of proxy server. (e.g. tcp://proxy.example.com:5100).
  private $proxy = NULL;

  // When set to TRUE, the entire URI will be used when constructing the request. While this is a non-standard request
  // format, some proxy servers require it.
  // todo Not used actually.
  private $requestFullUri = FALSE;

  // Socket connection timeout in seconds, specified by a float.
  private $timeout;

  // Socket or cURL handle.
  private $handle;

  private static $defaultSocketTimeout;

  // Used to know if the constructor has been already called.
  private static $initialized = FALSE;


  //! @brief Creates a Couch class instance.
  //! @param[in] string $server Server must be expressed as host:port as defined by RFC 3986. It's also possible specify
  //! a scheme like tcp://, ssl:// or tls://; if no scheme is present, tcp:// will be used.
  //! @param[in] string $userName (optional) User name.
  //! @param[in] string $password (optional) Password.
  //! @see http://www.ietf.org/rfc/rfc3986.txt
  public function __construct($server = self::DEFAULT_SERVER, $userName = "", $password = "") {
    self::initialize();

    // Parses the URI string '$server' to retrieve scheme, host and port and assigns matches to the relative class members.
    if (preg_match(self::SCHEME_HOST_PORT_URI, $server, $matches)) {
      $this->scheme = isset($matches['scheme']) ? $matches['scheme'] : "tcp://";
      $this->host = isset($matches['host']) ? $matches['host'] : "localhost";
      $this->port = isset($matches['port']) ? substr($matches['port'], 1) : "80";
    }
    else // Match attempt failed.
      throw new \InvalidArgumentException(sprintf("'%s' is not a valid URI.", $server));

    $this->userName = (string)$userName;
    $this->password = (string)$password;

    // Uses the default socket's timeout.
    $this->timeout = self::$defaultSocketTimeout;

    // PHP sockets are the default transport mode.
    if (self::$transport == self::SOCKET_TRANSPORT) {
      // Establishes a connection within the server.
      $this->handle = @pfsockopen($this->scheme.$this->host, $this->port, $errno, $errstr, $this->timeout);

      if (!is_resource($this->handle))
        throw new \ErrorException($errstr, $errno);
    }
    else {
      // Init cURL.
      $this->handle = curl_init();
    }
  }


  //! @brief Destroys the Couch class instance.
  public function __destruct() {
    if (self::$transport == self::CURL_TRANSPORT)
      curl_close($this->handle);
  }


  // We can avoid to call the following code every time a ElephantOnCouch instance is created, testing a static property.
  // Because the static nature of self::$initialized, this code will be executed only one time, even multiple Couch
  // instances are created.
  private static function initialize() {

    if (!self::$initialized) {
      self::$initialized = TRUE;

      // If PHP is not properly recognizing the line endings when reading files either on or created by a Macintosh
      // computer, enabling the auto_detect_line_endings run-time configuration option may help resolve the problem.
      ini_set("auto_detect_line_endings", TRUE);

      // By default the default_socket_timeout php.ini setting is used.
      self::$defaultSocketTimeout = ini_get("default_socket_timeout");
    }

  }


  private static function getCallerMethod() {
    $backtrace = debug_backtrace();

    var_dump($backtrace);

    if (array_key_exists("function", $backtrace))
      return $backtrace["function"];
    else
      return NULL;
  }


  // This method executes the provided request, using sockets.
  private function socketSend(Request $request, callable $chunkHookFn = NULL) {
    $command = $request->getMethod()." ".$request->getPath().$request->getQueryString()." ".self::HTTP_VERSION;

    $request->setHeaderField(Request::HOST_HF, $this->host.":".$this->port);

    if (!empty($this->userName))
      $request->setBasicAuth($this->userName, $this->password);

    // Sets the Content-Length header only when the given request has a message body.
    if ($request->hasBody())
      $request->setHeaderField(Message::CONTENT_LENGTH_HF, $request->getBodyLength());

    // Writes the request over the socket.
    fputs($this->handle, $command.self::CRLF);
    fputs($this->handle, $request->getHeaderAsString().self::CRLF);
    fputs($this->handle, self::CRLF);
    fputs($this->handle, $request->getBody());
    fputs($this->handle, self::CRLF);

    // Reads the header.
    $header = "";

    while (!feof($this->handle)) {
      // We use fgets() because it stops reading at first newline or buffer length, depends which one is reached first.
      $buffer = fgets($this->handle, self::BUFFER_LENGTH);

      // Adds the buffer to the header.
      $header .= $buffer;

      // The header is separated from the body by a newline, so we break when we read it.
      if ($buffer == self::CRLF)
        break;
    }

    // Creates the Response object, that parses the header.
    $response = new Response($header);

    // Now it's time to read the response body.
    $body = "";

    // This might be a chunked response. See: http://www.jmarshall.com/easy/http/#http1.1c2.
    if ($response->getHeaderFieldValue(Response::TRANSFER_ENCODING_HF) == "chunked") {

      while (!feof($this->handle)) {
        // Gets the line which has the length of this chunk.
        $line = fgets($this->handle, self::BUFFER_LENGTH);

        // If it's only a newline, this normally means it's read the total amount of data requested minus the newline
        // continue to next loop to make sure we're done.
        if ($line == self::CRLF)
          continue;

        // The length of the block is expressed in hexadecimal.
        $length = hexdec($line);

        if (!is_int($length))
          throw new \RuntimeException("The response doesn't seem chunk encoded.");

        // Zero is sent when at the end of the chunks or the end of the stream.
        if ($length < 1)
          break;

        // We use fread() here, because we know exactly how many bytes read.
        $buffer = fread($this->handle, $length);

        // If a function has been hooked, calls it, else just add the buffer to the body.
        if (is_null($chunkHookFn))
          $body .= $buffer;
        else
          call_user_func($chunkHookFn, $buffer);
      }

    }
    else { // Normal response, not chunked.
      // Retrieves the body length from the header.
      $length = (int)$response->getHeaderFieldValue(Response::CONTENT_LENGTH_HF);

      // The response should have a body, if not we have finished.
      if ($length > 0) {
        $bytes = 0;

        while (!feof($this->handle)) {
          $buffer = fgets($this->handle);
          $body .= $buffer;
          $bytes += strlen($buffer);

          if ($bytes >= $length)
            break;
        }
      }
    }

    // Assigns the body to the Response, if any is present.
    $response->setBody($body);

    return $response;
  }


  // This method executes the provided request, using cURL library. To use it, cURL must be installed on server.
  private function curlSend(Request $request, callable $chunkHookFn = NULL) {
    $opts = [];

    // Resets all the cURL options. The curl_reset() function is available only since PHP 5.5.
    if (function_exists('curl_reset'))
      curl_reset($this->handle);

    // Sets the methods and its related options.
    switch ($request->getMethod()) {

      // GET method.
      case Request::GET_METHOD:
        $opts[CURLOPT_HTTPGET] = TRUE;
        break;

      // POST method.
      case Request::POST_METHOD:
        // @bug The following instruction doesn't work; might be a cURL bug. We use, instead, CURLOPT_CUSTOMREQUEST.
        //$opts[CURLOPT_POST] = TRUE;

        $opts[CURLOPT_CUSTOMREQUEST] = Request::POST_METHOD;

        // The full data to post in a HTTP "POST" operation. To post a file, prepend a filename with @ and use the full
        // path. This can either be passed as a urlencoded string like 'para1=val1&para2=val2&...' or as an array with
        // the field name as key and field data as value. If value is an array, the Content-Type header will be set to
        // multipart/form-data.
        $opts[CURLOPT_POSTFIELDS] = ltrim($request->getQueryString(), "?");
        break;

      // PUT method.
      case Request::PUT_METHOD:
        $opts[CURLOPT_PUT] = TRUE;

        // Often a request contains data in the form of a JSON object. Since cURL is just able to read data from a file,
        // but we can't create a temporary file because it's a too much expensive operation, the code below uses a faster
        // and efficient memory stream.
        if ($request->hasBody()) {
          if ($fd = fopen("php://memory", "r+")) { // Try to create a temporary file in memory.
            fputs($fd, $request->getBody()); // Writes the message body.
            rewind($fd); // Sets the pointer to the beginning of the file stream.

            $opts[CURLOPT_INFILE] = $fd;
            $opts[CURLOPT_INFILESIZE] = $request->getBodyLength();
          }
          else
            throw new \RuntimeException("Cannot create the stream.");
        }

        break;

      // DELETE method.
      case Request::DELETE_METHOD:
        $opts[CURLOPT_CUSTOMREQUEST] = Request::DELETE_METHOD;
        break;

      // COPY or any other custom method.
      default:
        $opts[CURLOPT_CUSTOMREQUEST] = $request->getMethod();

    } // switch

    // Sets the request Uniform Resource Locator.
    $opts[CURLOPT_URL] = "http://".$this->host.":".$this->port.$request->getPath().$request->getQueryString();

    // Sets the request's header.
    $opts[CURLOPT_HTTPHEADER] = $request->getHeaderAsArray();

    // Includes the header in the output. We need this because our Response object will parse them.
    // NOTE: we don't include header anymore, because we use the option CURLOPT_HEADERFUNCTION.
    //$opts[CURLOPT_HEADER] = TRUE;

    // Returns the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
    $opts[CURLOPT_RETURNTRANSFER] = TRUE;

    // Sets the protocol version to be used. cURL constants have different values.
    $opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;

    // Sets basic authentication.
    if (!empty($this->userName)) {
      $opts[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
      $opts[CURLOPT_USERPWD] = $this->userName.":".$this->password;
    }

    // Sets the previous options.
    curl_setopt_array($this->handle, $opts);

    // This fix a known cURL bug: see http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
    // cURL sets the Expect header field automatically, ignoring the fact that a client may not need it for the specific
    // request.
    if (!$request->hasHeaderField(Request::EXPECT_HF))
      curl_setopt($this->handle, CURLOPT_HTTPHEADER, array("Expect:"));

    // Here we use this option because we might have a response without body. This may happen because we are supporting
    // chunk responses, and sometimes we want trigger an hook function to let the user perform operations on coming
    // chunks.
    $header = "";
    curl_setopt($this->handle, CURLOPT_HEADERFUNCTION, function($unused, $buffer) use (&$header) {
      $header .= $buffer;

      return strlen($buffer);
    });

    // When the hook function is provided, we set the CURLOPT_WRITEFUNCTION so cURL will call the hook function for each
    // response chunk read.
    if (isset($chunkHookFn)) {
      curl_setopt($this->handle, CURLOPT_WRITEFUNCTION, function($unused, $buffer) use ($chunkHookFn) {
        call_user_func($chunkHookFn, $buffer);

        return strlen($buffer);
      });
    }

    if ($result = curl_exec($this->handle)) {
      $response = new Response($header);
      $response->setBody($result);
      return $response;
    }
    else {
      $error = curl_error($this->handle);
      throw new \RuntimeException($error);
    }
  }


  private function doReplication($sourceDbUrl, $targetDbUrl, $createTargetDb = TRUE,
                                 $continuous = FALSE, $filter = NULL, Opt\ViewQueryOpts $opts = NULL) {
    // Sets the common parameters.
    if (is_string($sourceDbUrl) && !empty($sourceDbUrl) &&
      is_string($targetDbUrl) && !empty($targetDbUrl)) {
      $body["source"] = $sourceDbUrl;
      $body["target"] = $targetDbUrl;
    }
    else
      throw new \InvalidArgumentException("\$source_db_url and \$target_db_url must be non-empty strings.");

    if (!is_bool($continuous))
      throw new \InvalidArgumentException("\$continuous must be a boolean.");
    elseif ($continuous)
      $body["continuous"] = $continuous;

    // Uses the specified proxy if any set.
    if (isset($this->proxy))
      $body["proxy"] = $this->proxy;

    // Specific parameters depend by caller method.
    $callerMethod = self::getCallerMethod();

    if ($callerMethod == "startDbReplication") {
      // create_target option
      if (!is_bool($createTargetDb))
        throw new \InvalidArgumentException("\$createTargetDb must be a boolean.");
      elseif ($createTargetDb)
        $body["create_target"] = $createTargetDb;

      if (!empty($filter)) {
        if (is_string($filter)) // filter option
        $body["filter"] = $filter;
        elseif (is_array($filter)) // doc_ids option
        $body["doc_ids"] = array_values($filter);
        else
          throw new \InvalidArgumentException("\$filter must be a string or an array.");
      }

      // queryParams option
      if (!is_null($opts)) {
        if ($opts instanceof Opt\ViewQueryOpts)
          $body["queryParams"] = get_object_vars($opts);
        else
          throw new \InvalidArgumentException("\$queryParams must be an instance of ViewQueryOpts class.");
      }
    }
    elseif ($callerMethod == "cancelDbReplication") {
      $body["cancel"] = TRUE;
    }
    else
      throw new \Exception("realDbReplication can be called only from startDbReplication and cancelDbReplication methods.");

    return $this->send(Request::POST_METHOD, "/_replicate", NULL, NULL, $body);
  }


  //! @brief This method is used to send a Request to CouchDB.
  public function send(Request $request, callable $chunkHookFn = NULL) {
    // Sets user agent information.
    $request->setHeaderField(Request::USER_AGENT_HF, self::USER_AGENT_NAME." ".self::USER_AGENT_VERSION);

    // We accept JSON.
    $request->setHeaderField(Request::ACCEPT_HF, "application/json");

    // We close the connection after read the response.
    // NOTE: we don't use anymore the connection header field, because we use the same socket until the end of script.
    //$request->setHeaderField(Message::CONNECTION_HF, "close");

    if (self::$transport === self::SOCKET_TRANSPORT)
      $response = $this->socketSend($request, $chunkHookFn);
    else
      $response = $this->curlSend($request, $chunkHookFn);

    // 1xx - Informational Status Codes
    // 2xx - Success Status Codes
    // 3xx - Redirection Status Codes
    // 4xx - Client Error Status Codes
    // 5xx - Server Error Status Codes
    $statusCode = (int)$response->getStatusCode();

    switch ($statusCode) {
      case ($statusCode >= 200 && $statusCode < 300):
        break;
      case ($statusCode < 200):
        //$this->handleInformational($request, $response);
        break;
      case ($statusCode < 300):
        //$this->handleRedirection($request, $response);
        break;
      case ($statusCode < 400):
        throw new Exception\ClientErrorException($request, $response);
      case ($statusCode < 500):
        throw new Exception\ServerErrorException($request, $response);
      default:
        throw new Exception\UnknownResponseException($request, $response);
        break;
    }

    return $response;
  }


  //! @name Transport Mode Selection Methods
  //@{

  //! @brief Selects the cURL transport method.
  public static function useCurl() {
    if (extension_loaded("curl"))
      self::$transport = self::CURL_TRANSPORT;
    else
      throw new \RuntimeException("The cURL extension is not loaded.");
  }


  //! @brief Selects socket transport method. This is the default transport method.
  public static function useSocket() {
    self::$transport = self::SOCKET_TRANSPORT;
  }


  //! @brief Returns the active transport method.
  public function getTransportMethod() {
    return self::$transport;
  }

  //! @}


  //! @name Proxy Selection Methods
  //@{

  //! @brief Uses the specified proxy.
  public function setProxy($proxyAddress) {
    if (!empty($proxyAddress)) // todo Add a regex.
    $this->proxy = $proxyAddress;
    else
      throw new \InvalidArgumentException("The \$proxy is not valid.");
  }


  //! @brief Don't use any proxy.
  public function unsetProxy() {
    $this->proxy = NULL;
  }

  //! @}


  //! @name Validation and Encoding Methods
  // @{

  //! @brief This method raise an exception when a user provide an invalid document path.
  //! @details This method is called by any other methods that interacts with CouchDB. You don't need to call, unless
  //! you are making a not supported call to CouchDB.
  //! @param[in] string $path Document path.
  //! @param[in] boolean $excludeLocal Document path.
  public function validateDocPath($path, $excludeLocal = FALSE) {
    if (!array_key_exists($path, self::$supportedDocPaths))
      throw new \InvalidArgumentException("Invalid document path.");

    if ($excludeLocal && ($path == self::LOCAL_DOC_PATH))
      throw new \InvalidArgumentException("Local document doesn't have attachments.");
  }


  //! @brief This method raise an exception when a user provide an invalid database name.
  //! @details This method is called by any other methods that interacts with CouchDB. You don't need to call, unless
  //! you are making a not supported call to CouchDB.
  //! @param string $name Database name.
  public function validateAndEncodeDbName(&$name) {
    # \A[a-z][a-z\d_$()+-/]++\z
    #
    # Assert position at the beginning of the string «\A»
    # Match a single character in the range between “a” and “z” «[a-z]»
    # Match a single character present in the list below «[a-z\d_$()+-/]++»
    #    Between one and unlimited times, as many times as possible, without giving back (possessive) «++»
    #    A character in the range between “a” and “z” «a-z»
    #    A single digit 0..9 «\d»
    #    One of the characters “_$()” «_$()»
    #    A character in the range between “+” and “/” «+-/»
    # Assert position at the very end of the string «\z»
    if (preg_match('%\A[a-z][a-z\d_$()+-/]++\z%', $name))
      return $name = rawurlencode($name);
    else
      throw new \InvalidArgumentException("Invalid database name.");
  }


  //! @brief This method raise an exception when the user provides an invalid document identifier.
  //! @details This method is called by any other methods that interacts with CouchDB. You don't need to call, unless
  //! you are making a not supported call to CouchDB.
  //! @param string $docId Document id.
  public function validateAndEncodeDocId(&$docId) {
    # \A[\w_-]++\z
    #
    # Options: case insensitive
    #
    # Assert position at the beginning of the string «\A»
    # Match a single character present in the list below «[\w_-]++»
    #    Between one and unlimited times, as many times as possible, without giving back (possessive) «++»
    #    A word character (letters, digits, and underscores) «\w»
    #    The character “_” «_»
    #    The character “-” «-»
    # Assert position at the very end of the string «\z»
    if (preg_match('/\A[\w_-]++\z/i', $docId))
      $docId = rawurlencode($docId);
    else
      throw new \InvalidArgumentException("You must provide a valid \$docId.");
  }

  //! @}


  //! @name Server-level Miscellaneous Methods
  // @{

  //! @brief Creates the admin user.
  public function createAdminUser() {

  }


  //! @brief Restarts the server.
  //! @attention Requires admin privileges.
  //! @bug <a href="https://issues.apache.org/jira/browse/COUCHDB-947" target="_blank">COUCHDB-947</a>
  public function restartServer() {
    $request = new Request(Request::POST_METHOD, "/_restart");
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    // There is a bug in CouchDB, sometimes it doesn't return the 200 Status Code because it closes the connection
    // before the client has received the entire response. To avoid problems, we trap the exception and we go on.
    try {
      $this->send($request);
    }
    catch (\Exception $e) {
      if ($e->getCode() > 0)
        throw $e;
    }
  }


  //! @brief Returns an object that contains MOTD, server and client and PHP versions.
  //! @details The MOTD can be specified in CouchDB configuration files. This function returns more information
  //! compared to the CouchDB standard REST call.
  //! @code
  //! <?php
  //!
  //! use Couch\Client;
  //!
  //! $couch = new Couch(Couch::DEFAULT_SERVER, "user", "password");
  //! $couch->selectDb("database");
  //!
  //! try {
  //!   $info = $couch->getSvrInfo();
  //!   print_r($info);
  //! }
  //! catch (Exception $e) {
  //!   echo $e;
  //! }
  //! @endcode
  //! @return SvrInfo
  //! @see http://docs.couchdb.org/en/latest/api/misc.html#get
  public function getSvrInfo() {
    $response = $this->send(new Request(Request::GET_METHOD, "/"));
    $info = $response->getBodyAsArray();
    return new Info\SvrInfo($info["couchdb"], $info["version"]);
  }


  //! @brief Returns the favicon.ico file.
  //! @details The favicon is a part of the admin interface, but the handler for it is special as CouchDB tries to make
  //! sure that the favicon is cached for one year. Returns a string that represents the icon.
  //! @return string
  //! @see http://docs.couchdb.org/en/latest/api/misc.html#get-favicon-ico
  public function getFavicon() {
    $response = $this->send(new Request(Request::GET_METHOD, "/favicon.ico"));

    if ($response->getHeaderFieldValue(Request::CONTENT_TYPE_HF) == "image/x-icon")
      return $response->getBody();
    else
      throw new \InvalidArgumentException("Content-Type must be image/x-icon.");
  }


  //! @brief Returns server statistics.
  //! @return associative array
  //! @see http://docs.couchdb.org/en/latest/api/misc.html#get-stats
  public function getStats() {
    return $this->send(new Request(Request::GET_METHOD, "/_stats"))->getBodyAsArray();
  }


  //! @brief Returns a list of all databases on this server.
  //! @return array of string
  //! @see http://docs.couchdb.org/en/latest/api/misc.html#get-all-dbs
  public function getAllDbs() {
    return $this->send(new Request(Request::GET_METHOD, "/_all_dbs"))->getBodyAsArray();
  }


  //! @brief Returns a list of running tasks.
  //! @attention Requires admin privileges.
  //! @return associative array
  //! @see http://docs.couchdb.org/en/latest/api/misc.html#get-active-tasks
  public function getActiveTasks() {
    return $this->send(new Request(Request::GET_METHOD, "/_active_tasks"))->getBodyAsArray();
  }


  //! @brief Returns the tail of the server's log file.
  //! @attention Requires admin privileges.
  //! @param[in] integer $bytes How many bytes to return from the end of the log file.
  //! @return string
  //! @see http://docs.couchdb.org/en/latest/api/misc.html#get-log
  public function getLogTail($bytes = 1000) {
    if (is_int($bytes) and ($bytes > 0)) {
      $request = new Request(Request::GET_METHOD, "/_log");
      $request->setQueryParam("bytes", $bytes);
      return $this->send($request)->getBody();
    }
    else
      throw new \InvalidArgumentException("\$bytes must be a positive integer.");
  }


  //! @brief Returns a list of generated UUIDs.
  //! @param[in] integer $count Requested UUIDs number.
  //! @return string|array If <i>$count = 1</i> (default) returns a string else returns an array of strings.
  //! @see http://docs.couchdb.org/en/latest/api/misc.html#get-uuids
  public function getUuids($count = 1) {
    if (is_int($count) and ($count > 0)) {
      $request = new Request(Request::GET_METHOD, "/_uuids");
      $request->setQueryParam("count", $count);

      $response = $this->send($request);

      if ($count == 1) // We don't need to use === operator because, just above, we made a type checking.
        return $response->getBodyAsArray()['uuids'][0];
      else
        return $response->getBodyAsArray()['uuids'];
    }
    else
      throw new \InvalidArgumentException("\$count must be a positive integer.");
  }

  //@}


  //! @name Server Configuration Methods
  // @{

  //! @brief Returns the entire server configuration or a single section or a single configuration value of a section.
  //! @param[in] string $section Requested section.
  //! @param[in] string $key Requested key.
  //! @return string|array An array with the configuration keys or a simple string in case of a single key.
  //! @see http://docs.couchdb.org/en/latest/api/configuration.html#get-config
  //! @see http://docs.couchdb.org/en/latest/api/configuration.html#get-config-section
  //! @see http://docs.couchdb.org/en/latest/api/configuration.html#get-config-section-key
  public function getConfig($section = "", $key = "") {
    $path = "/_config";

    if (!empty($section)) {
      $path .= "/".$section;

      if (!empty($key))
        $path .= "/".$key;
    }

    return $this->send(new Request(Request::GET_METHOD, $path))->getBodyAsArray();
  }


  //! @brief Sets a single configuration value in a given section to server configuration.
  //! @param[in] string $section The configuration section.
  //! @param[in] string $key The key.
  //! @param[in] string $value The value for the key.
  //! @see http://docs.couchdb.org/en/latest/api/configuration.html#put-config-section-key
  public function setConfigKey($section, $key, $value) {
    if (!is_string($section) or empty($section))
      throw new \InvalidArgumentException("\$section must be a not empty string.");

    if (!is_string($key) or empty($key))
      throw new \InvalidArgumentException("\$key must be a not empty string.");

    if (is_null($value))
      throw new \InvalidArgumentException("\$value cannot be null.");

    $request = new Request(Request::PUT_METHOD, "/_config/".$section."/".$key);
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");
    $request->setBody(json_encode(utf8_encode($value)));
    $this->send($request);
  }


  //! @brief Deletes a single configuration value from a given section in server configuration.
  //! @param[in] string $section The configuration section.
  //! @param[in] string $key The key.
  //! @see http://docs.couchdb.org/en/latest/api/configuration.html#delete-config-section-key
  public function deleteConfigKey($section, $key) {
    if (!is_string($section) or empty($section))
      throw new \InvalidArgumentException("\$section must be a not empty string.");

    if (!is_string($key) or empty($key))
      throw new \InvalidArgumentException("\$key must be a not empty string.");

    $this->send(new Request(Request::DELETE_METHOD, "/_config/".$section."/".$key));
  }

  //@}


  //! @name Authentication Methods
  // @{

  //! @brief Returns cookie based login user information.
  //! @return Response
  //! @see http://wiki.apache.org/couchdb/Session_API
  public function getSession() {
    return $this->send(new Request(Request::GET_METHOD, "/_session"));
  }


  //! @brief Makes cookie based user login.
  //! @return Response
  //! @see http://wiki.apache.org/couchdb/Session_API
  public function setSession($userName, $password) {
    if (!is_string($userName) or empty($userName))
      throw new \InvalidArgumentException("\$userName must be a not empty string.");

    if (!is_string($password) or empty($password))
      throw new \InvalidArgumentException("\$password must be a not empty string.");

    $request = new Request(Request::POST_METHOD, "/_session");

    $request->setHeaderField(Request::X_COUCHDB_WWW_AUTHENTICATE_HF, "Cookie");
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/x-www-form-urlencoded");

    $request->setQueryParam("name", $userName);
    $request->setQueryParam("password", $password);

    return $this->send($request);
  }


  //! @brief Makes user logout.
  //! @return a Response object
  //! @see http://wiki.apache.org/couchdb/Session_API
  public function deleteSession() {
    return $this->send(new Request(Request::DELETE_METHOD, "/_session"));
  }


  //! @brief
  //! @see http://wiki.apache.org/couchdb/Session_API
  public function getAccessToken() {
    return $this->send(new Request(Request::GET_METHOD, "/_oauth/access_token"));
  }


  //! @brief
  //! @see http://wiki.apache.org/couchdb/Security_Features_Overview#Authorization
  public function getAuthorize() {
    return $this->send(new Request(Request::GET_METHOD, "/_oauth/authorize"));
  }


  //! @brief
  //! http://wiki.apache.org/couchdb/Security_Features_Overview#Authorization
  public function setAuthorize() {
    return $this->send(new Request(Request::POST_METHOD, "/_oauth/authorize"));
  }


  // @brief
  public function requestToken() {
    return $this->send(new Request(Request::GET_METHOD, "/_oauth/request_token"));
  }

  //@}


  //! @name Database-level Miscellaneous Methods
  // @{

  //! @brief Sets the database name to use.
  //! @details You should call this method before just after the constructor. CouchDB is a RESTful server implementation,
  //! that means that you can't establish a permanent connection with it, but you just call APIs through HTTP requests.
  //! In every call you have to specify the database name (when a database is required). The ElephantOnCouch client stores this
  //! information for us, so we don't need to pass the database name as parameter to every method call. The purpose of
  //! this method, is to avoid you repeat database name every time. The function doesn't check if the database really
  //! exists, but it performs a fast check on the name itself. To obtain information about a database, use getDbInfo
  //! instead.
  //! @attention Only lowercase characters (a-z), digits (0-9), and any of the characters _, $, (, ), +, -, and / are
  //! allowed. Must begin with a letter.</i></c>\n
  //! @param[in] string $name Database name.
  public function selectDb($name) {
    $this->dbName = $this->validateAndEncodeDbName($name);
  }


  //! @brief Check if a database has been selected.
  //! @details This method is called by any other methods that interacts with CouchDB. You don't need to call, unless
  //! you are making a not supported call to CouchDB.
  public function checkForDb() {
    if (empty($this->dbName))
      throw new \RuntimeException("No database selected.");
  }


  //! @brief Creates a new database and selects it.
  //! @param[in] string $name The database name. A database must be named with all lowercase letters (a-z),
  //! digits (0-9), or any of the _$()+-/ characters and must end with a slash in the URL. The name has to start with a
  //! lowercase letter (a-z).
  //! @param[in] bool $autoSelect Selects the created database by default.
  //! @see http://docs.couchdb.org/en/latest/api/database.html#put-db
  public function createDb($name, $autoSelect = TRUE) {
    $this->validateAndEncodeDbName($name);

    if ($name != $this->dbName) {
      $this->send(new Request(Request::PUT_METHOD, "/".rawurlencode($name)."/"));

      if ($autoSelect)
        $this->dbName = $name;
    }
    else
      throw new \UnexpectedValueException("You can't create a database with the same name of the selected database.");
  }


  //! @brief Deletes an existing database.
  //! @param[in] string $name The database name. A database must be named with all lowercase letters (a-z),
  //! digits (0-9), or any of the _$()+-/ characters and must end with a slash in the URL. The name has to start with a
  //! lowercase letter (a-z).
  //! @see http://docs.couchdb.org/en/latest/api/database.html#delete-db
  public function deleteDb($name) {
    $this->validateAndEncodeDbName($name);

    if ($name != $this->dbName)
      $this->send(new Request(Request::DELETE_METHOD, "/".$name));
    else
      throw new \UnexpectedValueException("You can't delete the selected database.");
  }


  //! @brief Returns information about the selected database.
  //! @return DbInfo
  public function getDbInfo() {
    $this->checkForDb();

    return new Info\Dbinfo($this->send(new Request(Request::GET_METHOD, "/".$this->dbName."/"))->getBodyAsArray());
  }


  //! @brief Obtains a list of the changes made to the database. This can be used to monitor for update and modifications
  //! to the database for post processing or synchronization.
  //! @return A Response object.
  //! @see http://docs.couchdb.org/en/latest/api/database.html#get-db-changes
  public function getDbChanges(Opt\ChangesFeedOpts $opts = NULL) {
    $this->checkForDb();

    $request = new Request(Request::GET_METHOD, "/".$this->dbName."/_changes");

    if (isset($opts))
      $request->setMultipleQueryParamsAtOnce($opts->asArray());

    return $this->send($request)->getBodyAsArray();
  }


  //! @brief Starts a compaction for the current selected database.
  //! @details Writes a new version of the database file, removing any unused sections from the new version during write.
  //! Because a new file is temporary created for this purpose, you will need twice the current storage space of the
  //! specified database in order for the compaction routine to complete.<br />
  //! Removes old revisions of documents from the database, up to the per-database limit specified by the <i>_revs_limit</i>
  //! database setting.<br />
  //! Compaction can only be requested on an individual database; you cannot compact all the databases for a CouchDB
  //! instance.<br />
  //! The compaction process runs as a background process. You can determine if the compaction process is operating on a
  //! database by obtaining the database meta information, the <i>compact_running</i> value of the returned database
  //! structure will be set to true. You can also obtain a list of running processes to determine whether compaction is
  //! currently running, using getActiveTasks().
  //! @attention Requires admin privileges.
  //! @see http://docs.couchdb.org/en/latest/api/database.html#post-db-compact
  public function compactDb() {
    $this->checkForDb();

    $request = new Request(Request::POST_METHOD, "/".$this->dbName."/_compact");

    // A POST method requires Content-Type header.
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    $this->send($request);
  }


  //! @brief Compacts the specified view.
  //! @details If you have very large views or are tight on space, you might consider compaction as well. To run compact
  //! for a particular view on a particular database, use this method.
  //! @param[in] string $designDocName Name of the design document where is stored the view.
  //! @see http://docs.couchdb.org/en/latest/api/database.html#post-db-compact-design-doc
  public function compactView($designDocName) {
    $this->checkForDb();

    $path = "/".$this->dbName."/_compact/".$designDocName;

    $request = new Request(Request::POST_METHOD, $path);

    // A POST method requires Content-Type header.
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    $this->send($request);
  }


  //! @brief Removes all outdated view indexes.
  //! @details Old views files remain on disk until you explicitly run cleanup.
  //! @attention Requires admin privileges.
  //! @see http://docs.couchdb.org/en/latest/api/database.html#post-db-view-cleanup
  public function cleanupViews() {
    $this->checkForDb();

    $request =  new Request(Request::POST_METHOD, "/".$this->dbName."/_view_cleanup");

    // A POST method requires Content-Type header.
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    $this->send($request);
  }


  //! @brief Makes sure all uncommited database changes are written and synchronized to the disk.
  //! @details Default CouchDB configuration use delayed commit to improve performances. So CouchDB allows operations to
  //! be run against the disk without an explicit fsync after each operation. Synchronization takes time (the disk may
  //! have to seek, on some platforms the hard disk cache buffer is flushed, etc.), so requiring an fsync for each update
  //! deeply limits CouchDB's performance for non-bulk writers.<br />
  //! Delayed commit should be left set to true in the configuration settings. Anyway, you can still tell CouchDB to make
  //! an fsync, calling the ensure_full_commit method.
  //! @return string A timestamp when the server instance was started.
  //! @see http://docs.couchdb.org/en/latest/api/database.html#post-db-ensure-full-commit
  public function ensureFullCommit() {
    $this->checkForDb();

    $request = new Request(Request::POST_METHOD, "/".$this->dbName."/_ensure_full_commit");

    // A POST method requires Content-Type header.
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    return $this->send($request)->getBodyAsArray()["instance_start_time"];
  }


  //! @brief Returns the special security object for the database.
  //! @details
  //! @return
  //! @see http://docs.couchdb.org/en/latest/api/database.html#get-db-security
  public function getSecurityObj() {
    $this->checkForDb();

    return $this->send(new Request(Request::GET_METHOD, "/".$this->dbName."/_security"));
  }


  //! @brief Sets the special security object for the database.
  //! @details
  //! @return
  //! @see http://docs.couchdb.org/en/latest/api/database.html#put-db-security
  public function setSecurityObj() {
    $this->checkForDb();

    return $this->send(new Request(Request::PUT_METHOD, "/".$this->dbName."/_security"));
  }

  //@}


  //! @name Database Replication Methods
  //! @details The replication is an incremental one way process involving two databases (a source and a destination).
  //! The aim of the replication is that at the end of the process, all active documents on the source database are also
  //! in the destination database and all documents that were deleted in the source databases are also deleted (if
  //! exists) on the destination database.<br />
  //! The replication process only copies the last revision of a document, so all previous revisions that were only on
  //! the source database are not copied to the destination database.<br />
  //! Changes on the master will not automatically replicate to the slaves. To make replication continuous, you must set
  //! <i>\$continuous = TRUE</i>. At this time, CouchDB does not remember continuous replications over a server restart.
  //! Specifying a local source database and a remote target database is called push replication and a remote source and
  //! local target is called pull replication. As of CouchDB 0.9, pull replication is a lot more efficient and resistant
  //! to errors, and it is suggested that you use pull replication in most cases, especially if your documents are large
  //! or you have large attachments.
  // @{

  //! @brief Starts replication.
  //! @code startReplication("sourcedbname", "http://example.org/targetdbname", TRUE, TRUE); @endcode
  //! @param[in] string $sourceDbUrl todo
  //! @param[in] string $targetDbUrl
  //! @param[in] boolean $createTargetDb The target database has to exist and is not implicitly created. You can force
  //! the creation setting <i>\$createTargetDb = TRUE</i>.<br />
  //! @param[in] boolean $continuous When you set <i>\$continuous = TRUE</i> CouchDB will not stop after replicating all
  //! missing documents from the source to the target.<br />
  //! At the time of writing, CouchDB doesn't remember continuous replications over a server restart. For the time being,
  //! you are required to trigger them again when you restart CouchDB. In the future, CouchDB will allow you to define
  //! permanent continuous replications that survive a server restart without you having to do anything.
  //! @param[in] string|array $filter todo
  //! @param[in] ViewQueryOpts $opts todo
  //! @see http://docs.couchdb.org/en/latest/api/misc.html#post-replicate
  //! @todo Document parameters.
  public function startReplication($sourceDbUrl, $targetDbUrl, $createTargetDb = TRUE,
                                     $continuous = FALSE, $filter = NULL, Opt\ViewQueryOpts $opts = NULL) {
    return $this->doReplication($sourceDbUrl, $targetDbUrl, $createTargetDb, $continuous, $filter, $opts);
  }


  //! @brief Cancels replication.
  //! @see http://docs.couchdb.org/en/latest/api/misc.html#post-replicate
  //! @todo Document parameters.
  public function stopReplication($sourceDbUrl, $targetDbUrl, $continuous = FALSE) {
    return $this->doReplication($sourceDbUrl, $targetDbUrl, $continuous);
  }


  //! @brief
  //! @details
  //! @see http://wiki.apache.org/couchdb/Replication#Replicator_database
  //! @see http://docs.couchbase.org/couchdb-release-1.1/index.html#couchb-release-1.1-replicatordb
  //! @see https://gist.github.com/832610
  public function getReplicator() {

  }

  //@}


  //! @name Query Documents Methods
  // @{

  //! @brief Returns a built-in view of all documents in this database. If keys are specified returns only certain rows.
  //! @param[in] string $designDocName The design document's name.
  //! @param[in] string $viewName The view's name.
  //! @param[in] \ArrayIterator $keys (optional) Used to retrieve just the view rows matching that set of keys. Rows are returned
  //! in the order of the specified keys. Combining this feature with include_docs=true results in the so-called
  //! multi-document-fetch feature.
  //! @param[in] ViewQueryOpts $opts (optional) Query options to get additional information, grouping results, include
  //! docs, etc.
  //! @return associative array
  //! @see http://docs.couchdb.org/en/latest/api/database.html#get-db-all-docs
  //! @see http://docs.couchdb.org/en/latest/api/database.html#post-db-all-docs
  public function queryAllDocs(\ArrayIterator $keys = NULL, Opt\ViewQueryOpts $opts = NULL) {
    $this->checkForDb();

    if (is_null($keys))
      $request = new Request(Request::GET_METHOD, "/".$this->dbName."/_all_docs");
    else {
      $request = new Request(Request::POST_METHOD, "/".$this->dbName."/_all_docs");
      $request->setBody(json_encode(utf8_encode(['keys' => $keys])));
    }

    if (isset($opts))
      $request->setMultipleQueryParamsAtOnce($opts->asArray());

    return $this->send($request);
  }


  //! @brief Executes the given view and returns the result.
  //! @param[in] string $designDocName The design document's name.
  //! @param[in] string $viewName The view's name.
  //! @param[in] \ArrayIterator $keys (optional) Used to retrieve just the view rows matching that set of keys. Rows are returned
  //! in the order of the specified keys. Combining this feature with include_docs=true results in the so-called
  //! multi-document-fetch feature.
  //! @param[in] ViewQueryOpts $opts (optional) Query options to get additional information, grouping results, include
  //! docs, etc.
  //! @return associative array
  //! @see http://docs.couchdb.org/en/latest/api/design.html#get-db-design-design-doc-view-view-name
  //! @see http://docs.couchdb.org/en/latest/api/design.html#post-db-design-design-doc-view-view-name
  public function queryView($designDocName, $viewName, \ArrayIterator $keys = NULL, Opt\ViewQueryOpts $opts = NULL) {
    $this->checkForDb();

    $this->validateAndEncodeDocId($designDocName);

    if (empty($viewName))
      throw new \InvalidArgumentException("You must provide a valid \$viewName.");

    if (is_null($keys))
      $request = new Request(Request::GET_METHOD, "/".$this->dbName."/_design/".$designDocName."/_view/".$viewName);
    else {
      $request = new Request(Request::POST_METHOD, "/".$this->dbName."/_design/".$designDocName."/_view/".$viewName);
      $request->setBody(json_encode(utf8_encode(['keys' => $keys])));
    }

    if (isset($opts))
      $request->setMultipleQueryParamsAtOnce($opts->asArray());

    return $this->send($request);
  }


  //! @brief Executes the given view, both map and reduce functions, for all documents and returns the result.
  //! @details Map and Reduce functions are provided by the programmer.
  //! @attention Requires admin privileges.
  //! @param[in] string $designDocName The design document's name.
  //! @param[in] string $viewName The view's name.
  //! @param[in] \ArrayIterator $keys (optional) Used to retrieve just the view rows matching that set of keys. Rows are returned
  //! in the order of the specified keys. Combining this feature with include_docs=true results in the so-called
  //! multi-document-fetch feature.
  //! @param[in] ViewQueryOpts $opts (optional) Query options to get additional information, grouping results, include
  //! docs, etc.
  //! @return associative array
  //! @see http://docs.couchdb.org/en/latest/api/database.html#post-db-temp-view
  public function queryTempView($mapFn, $reduceFn = "", \ArrayIterator $keys = NULL, Opt\ViewQueryOpts $opts = NULL) {
    $this->checkForDb();

    $handler = new Handler\ViewHandler("temp");
    $handler->mapFn = $mapFn;
    if (!empty($reduce))
      $handler->reduceFn = $reduceFn;

    $request = new Request(Request::POST_METHOD, "/".$this->dbName."/_temp_view");

    if (is_null($keys))
      $request->setBody(json_encode($handler->asArray()));
    else {
      $body = $handler->asArray() + ['keys' => $keys];

      $request->setBody(json_encode(utf8_encode($body)));
    }

    if (isset($opts))
      $request->setMultipleQueryParamsAtOnce($opts->asArray());

    return $this->send($request);
  }

  //@}


  //! @name Revisions Management Methods
  // @{

  //! @brief Given a list of document revisions, returns the document revisions that do not exist in the database.
  //! @return Response
  //! @see http://docs.couchdb.org/en/latest/api/database.html#post-db-missing-revs
  public function getMissingRevs() {
    $this->checkForDb();

    $request = new Request(Request::POST_METHOD, "/".$this->dbName."/_missing_revs");

    return $this->send($request);
  }


  //! @brief Given a list of document revisions, returns differences between the given revisions and ones that are in
  //! the database.
  //! @return Response
  //! @see http://docs.couchdb.org/en/latest/api/database.html#post-db-revs-diff
  public function getRevsDiff() {
    $this->checkForDb();

    $request = new Request(Request::POST_METHOD, "/".$this->dbName."/_missing_revs");

    return $this->send($request);
  }


  //! @brief Gets the limit of historical revisions to store for a single document in the database.
  //! @return todo
  //! @see http://docs.couchdb.org/en/latest/api/database.html#get-db-revs-limit
  public function getRevsLimit() {
    $this->checkForDb();

    $request = new Request(Request::GET_METHOD, "/".$this->dbName."/_revs_limit");

    return $this->send($request);
  }


  //! @brief Sets the limit of historical revisions for a single document in the database.
  //! @param[in] integer $revsLimit (optional) Maximum number historical revisions for a single document in the database.
  //! Must be a positive integer.
  //! @see http://docs.couchdb.org/en/latest/api/database.html#put-db-revs-limit
  public function setRevsLimit($revsLimit = self::REVS_LIMIT) {
    $this->checkForDb();

    if (!is_int($revsLimit) or ($revsLimit <= 0))
      throw new \InvalidArgumentException("\$revsLimit must be a positive integer.");

    $request = new Request(Request::PUT_METHOD, "/".$this->dbName."/_revs_limit");
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");
    $request->setBody(json_encode($revsLimit));

    $this->send($request);
  }

  //@}


  //! @name Documents Management Methods
  // @{

  //! @brief Returns the document's entity tag, that can be used for caching or optimistic concurrency control purposes.
  //! The ETag Header is simply the document's revision in quotes.
  //! @details This function is not available for special documents. To get information about a design document, use
  //! the special function getDesignDocInfo().
  //! @param[in] string $docId The document's identifier.
  //! @return string The document's revision.
  //! @see http://docs.couchdb.org/en/latest/api/documents.html#head-db-doc
  public function getDocEtag($docId) {
    $this->checkForDb();

    $this->validateAndEncodeDocId($docId);

    $path = "/".$this->dbName."/".$docId;

    $request = new Request(Request::HEAD_METHOD, $path);

    // CouchDB ETag is included between quotation marks.
    return trim($this->send($request)->getHeaderFieldValue(Response::ETAG_HF), '"');
  }


  //! @brief Returns the latest revision of the document.
  //! @details Since CouchDB uses different paths to store special documents, you must provide the document type for
  //! design and local documents.
  //! @param[in] string $docId The document's identifier.
  //! @param[in] string $path The document's path.
  //! @param[in] string $rev (optional) The document's revision.
  //! @param[in] DocOpts $opts Query options to get additional document information, like conflicts, attachments, etc.
  //! @return object An instance of Doc, LocalDoc, DesignDoc or any subclass of Doc.
  //! @see http://docs.couchdb.org/en/latest/api/documents.html#get-db-doc
  public function getDoc($path, $docId, $rev = NULL, Opt\DocOpts $opts = NULL) {
    $this->checkForDb();
    $this->validateDocPath($path);
    $this->validateAndEncodeDocId($docId);

    $requestPath = "/".$this->dbName."/".$path.$docId;

    $request = new Request(Request::GET_METHOD, $requestPath);

    // Retrieves the specific revision of the document.
    if (!empty($rev))
      $request->setQueryParam("rev", (string)$rev);

    // If there are any options, add them to the request.
    if (isset($opts)) {
      $request->setMultipleQueryParamsAtOnce($opts->asArray());
      $ignoreClassName = $opts->ignoreClassName;
    }
    else
      $ignoreClassName = FALSE;

    $body = $this->send($request)->getBodyAsArray();

    // We use 'doc_class' metadata to store an instance of a specialized document class. We can have Article and Book classes,
    // both derived from Doc, with special properties and methods. Instead to convert them, we store the class type in a
    // special attribute called <i>AbstractDoc::DOC_CLASS</i> within the others metadata. So, once we retrieve the document,
    // the client creates an instance of the class we provided when we saved the document. We don't need to convert it.
    if (!$ignoreClassName && isset($body[Doc\AbstractDoc::DOC_CLASS])) { // Special document class inherited from Doc or LocalDoc.
      $type = "\\".$body[Doc\AbstractDoc::DOC_CLASS];
      $doc = new $type;
    }
    elseif ($path == self::LOCAL_DOC_PATH)   // Local document.
      $doc = new Doc\LocalDoc;
    elseif ($path == self::DESIGN_DOC_PATH)  // Design document.
      $doc = new Doc\DesignDoc;
    else                                     // Standard document.
      $doc = new Doc\Doc;

    $doc->assignArray($body);

    return $doc;
  }


  //! @brief Inserts or updates a document into the selected database.
  //! @details Whether the <i>\$doc</i> has an id we use a different HTTP method. Using POST CouchDB generates an id for the doc,
  //! using PUT instead we need to specify one. We can still use the function getUuids() to ask CouchDB for some ids.
  //! This is an internal detail. You have only to know that CouchDB can generate the document id for you.
  //! @param[in] Doc $doc The document you want insert or update.
  //! @param[in] bool $batch_mode You can write documents to the database at a higher rate by using the batch option. This
  //! collects document writes together in memory (on a user-by-user basis) before they are committed to disk.
  //! This increases the risk of the documents not being stored in the event of a failure, since the documents are not
  //! written to disk immediately.
  //! @see http://docs.couchdb.org/en/latest/api/documents.html#put-db-doc
  /// @todo Support the new_edits=true|false option, see http://wiki.apache.org/couchdb/HTTP_Bulk_Document_API#Posting_Existing_Revisions
  public function saveDoc(Doc\AbstractDoc $doc, $batchMode = FALSE) {
    $this->checkForDb();

    // We never use the POST method.
    $method = Request::PUT_METHOD;

    // Whether the document has an id we use a different HTTP method. Using POST CouchDB generates an id for the doc
    // using PUT we need to specify one. We can still use the function getUuids() to ask CouchDB for some ids.
    if (!$doc->issetId())
      $doc->setid(Generator\UUID::generate(Generator\UUID::UUID_RANDOM, Generator\UUID::FMT_STRING));

    // Sets the path according to the document type.
    if ($doc instanceof \ElephantOnCouch\Doc\DesignDoc)
      $path = "/".$this->dbName."/".self::DESIGN_DOC_PATH.$doc->getId();
    elseif ($doc instanceof \ElephantOnCouch\Doc\LocalDoc)
      $path = "/".$this->dbName."/".self::LOCAL_DOC_PATH.$doc->getId();
    else
      $path = "/".$this->dbName."/".$doc->getId();

    $request = new Request($method, $path);
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");
    $request->setBody($doc->asJson());

    // Enables batch mode.
    if ($batchMode)
      $request->setQueryParam("batch", "ok");

    $this->send($request);
  }


  //! @brief Deletes the specified document.
  //! @details To delete a document you must provide the document identifier and the revision number.
  //! @param[in] string $docId The document's identifier you want delete.
  //! @param[in] string $rev The document's revision number you want delete.
  //! @param[in] string $path The document path.
  //! @see http://docs.couchdb.org/en/latest/api/documents.html#delete-db-doc
  public function deleteDoc($path, $docId, $rev) {
    $this->checkForDb();
    $this->validateDocPath($path);
    $this->validateAndEncodeDocId($docId);

    $path = "/".$this->dbName."/".$path.rawurlencode($docId);

    $request = new Request(Request::DELETE_METHOD, $path);
    $request->setQueryParam("rev", (string)$rev);

    // We could use another technique to send the revision number. Here just for documentation.
    // $request->setHeader(Request::IF_MATCH_HEADER, (string)$rev);

    $this->send($request);
  }


  //! @brief Makes a duplicate of the specified document. If you want to overwrite an existing document, you need to
  //! specify the target document's revision with a <i>\$rev</i> parameter.
  //! @details If you want copy a special document you must specify his type.
  //! @param[in] string $sourceDocId The source document id.
  //! @param[in] string $targetDocId The destination document id.
  //! @param[in] string $rev Needed when you want override an existent document.
  //! @param[in] string $path The document path.
  //! @see http://docs.couchdb.org/en/latest/api/documents.html#copy-db-doc
  public function copyDoc($path, $sourceDocId, $targetDocId, $rev = NULL) {
    $this->checkForDb();
    $this->validateDocPath($path);

    $this->validateAndEncodeDocId($sourceDocId);
    $this->validateAndEncodeDocId($targetDocId);

    $path = "/".$this->dbName."/".$path.$sourceDocId;

    // This request uses the special method COPY.
    $request = new Request(Request::COPY_METHOD, $path);

    if (empty($rev))
      $request->setHeaderField(Request::DESTINATION_HF, $targetDocId);
    else
      $request->setHeaderField(Request::DESTINATION_HF, $targetDocId."?rev=".(string)$rev);

    $this->send($request);
  }


  //! @brief The purge operation removes the references to the deleted documents from the database.
  //! @details A database purge permanently removes the references to deleted documents from the database. Deleting a
  //! document within CouchDB does not actually remove the document from the database, instead, the document is marked as
  //! a deleted (and a new revision is created). This is to ensure that deleted documents are replicated to other
  //! databases as having been deleted. This also means that you can check the status of a document and identify that
  //! the document has been deleted.<br />
  //! The purging of old documents is not replicated to other databases. If you are replicating between databases and
  //! have deleted a large number of documents you should run purge on each database.<br />
  //! Purging documents does not remove the space used by them on disk. To reclaim disk space, you should run compactDb().
  //! @return Response
  //! @see http://docs.couchdb.org/en/latest/api/database.html#post-db-purge
  public function purgeDocs(array $docs) {
    $this->checkForDb();

    return $this->send(new Request(Request::POST_METHOD, "/".$this->dbName));
  }


  //! @brief Inserts, updates and deletes documents in a bulk.
  //! @details Documents that are updated or deleted must contain the <i>rev</i> number. To delete a document, you should set
  //! <i>delete = true</i>.
  public function performBulkOperations(array $docs, $fullCommit = FALSE) {
    $this->checkForDb();

    $path = "/".$this->dbName."/_bulk_docs";

    foreach ($docs as $doc) {
      $request = new Request(Request::POST_METHOD, $path);

      if ($fullCommit)
        $request->setHeaderField(Request::X_COUCHDB_FULL_COMMIT_HF, "full_commit");
      else
        $request->setHeaderField(Request::X_COUCHDB_FULL_COMMIT_HF, "delay_commit");

      $this->send($request);
    }
  }

  //@}


  //! @name Attachments Management Methods
  // @{

  //! @brief Retrieves the attachment from the specified document.
  //! @see http://docs.couchdb.org/en/latest/api/documents.html#get-db-doc-attachment
  //! @todo Document parameters.
  public function getAttachment($fileName, $path, $docId, $rev = NULL) {
    $this->checkForDb();
    $this->validateDocPath($path, TRUE);
    $this->validateAndEncodeDocId($docId);

    $path = "/".$this->dbName."/".$path.$docId."/".$fileName;

    $request = new Request(Request::GET_METHOD, $path);

    // In case we want retrieve a specific document revision.
    if (!empty($rev))
      $request->setQueryParam("rev", (string)$rev);

    return $this->send($request)->getBody();
  }


  //! @brief Inserts or updates an attachment to the specified document.
  //! @see http://docs.couchdb.org/en/latest/api/documents.html#put-db-doc-attachment
  //! @todo Document parameters.
  public function putAttachment($fileName, $path, $docId, $rev = NULL) {
    $this->checkForDb();
    $this->validateDocPath($path, TRUE);
    $this->validateAndEncodeDocId($docId);

    $attachment = Attachment\Attachment::fromFile($fileName);

    $path = "/".$this->dbName."/".$path.$docId."/".rawurlencode($attachment->getName());

    $request = new Request(Request::PUT_METHOD, $path);
    $request->setHeaderField(Request::CONTENT_LENGTH_HF, $attachment->getContentLength());
    $request->setHeaderField(Request::CONTENT_TYPE_HF, $attachment->getContentType());
    $request->setBody(base64_encode($attachment->getData()));

    // In case of adding or updating an existence document.
    if (!empty($rev))
      $request->setQueryParam("rev", (string)$rev);

    return $this->send($request);
  }


  //! @brief Deletes an attachment from the document.
  //! @see http://docs.couchdb.org/en/latest/api/documents.html#delete-db-doc-attachment
  //! @todo Document parameters.
  public function deleteAttachment($fileName, $path, $docId, $rev) {
    $this->checkForDb();
    $this->validateDocPath($path, TRUE);
    $this->validateAndEncodeDocId($docId);

    $path = "/".$this->dbName."/".$path.$docId."/".rawurlencode($fileName);

    $request = new Request(Request::DELETE_METHOD, $path);
    $request->setQueryParam("rev", (string)$rev);

    return $this->send($request);
  }

  //@}


  //! @name Special Design Documents Management Methods
  // @{

  //! @brief Returns basic information about the design document and his views.
  //! @param[in] string $docName The design document's name.
  //! @return associative array
  //! @see http://docs.couchdb.org/en/latest/api/design.html#get-db-design-design-doc-info
  public function getDesignDocInfo($docName) {
    $this->checkForDb();
    $this->validateAndEncodeDocId($docName);

    $path = "/".$this->dbName."/".self::DESIGN_DOC_PATH.$docName."/_info";

    $request = new Request(Request::GET_METHOD, $path);

    return $this->send($request)->getBodyAsArray();
  }


  //! @brief
  //! @details
  //! @see http://docs.couchdb.org/en/latest/api/design.html#get-db-design-design-doc-show-show-name
  //! @see http://docs.couchdb.org/en/latest/api/design.html#post-db-design-design-doc-show-show-name-doc
  public function showDoc($designDocName, $showName, $docId = NULL) {
    // Invokes the show handler without a document
    // /db/_design/design-doc/_show/show-name
    // Invokes the show handler for the given document
    // /db/_design/design-doc/_show/show-name/doc
    // GET /db/_design/examples/_show/posts/somedocid
    // GET /db/_design/examples/_show/people/otherdocid
    // GET /db/_design/examples/_show/people/otherdocid?format=xml&details=true
    // public function showDoc($designDocName, $funcName, $docId, $format, $details = FALSE) {
  }

  //! @brief
  //! @details
  //! @see
  public function listDocs($designDocName, $listName, $docId = NULL) {
    // Invokes the list handler to translate the given view results
    // Invokes the list handler to translate the given view results for certain documents
    // GET /db/_design/examples/_list/index-posts/posts-by-date?descending=true&limit=10
    // GET /db/_design/examples/_list/index-posts/posts-by-tag?key="howto"
    // GET /db/_design/examples/_list/browse-people/people-by-name?startkey=["a"]&limit=10
    // GET /db/_design/examples/_list/index-posts/other_ddoc/posts-by-tag?key="howto"
    // public function listDocs($designDocName, $funcName, $viewName, $queryArgs, $keys = "") {
  }


  //! @brief
  //! @details
  //! @see
  public function callUpdateDocFunc($designDocName, $funcName) {
    // Invokes the update handler without a document
    // /db/_design/design-doc/_update/update-name
    // Invokes the update handler for the given document
    // /db/_design/design-doc/_update/update-name/doc
    // a PUT request against the handler function with a document id: /<database>/_design/<design>/_update/<function>/<docid>
    // a POST request against the handler function without a document id: /<database>/_design/<design>/_update/<function>
  }


  // Invokes the URL rewrite handler and processes the request after rewriting
  // THIS FUNCTION MAKES NO SENSE
  //public function rewriteUrl($designDocName) {
    // /db/_design/design-doc/_rewrite
  //}

  //@}

}