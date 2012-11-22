<?php

//! @file ElephantOnCouch.php
//! @brief This file contains the ElephantOnCouch class.
//! @details
//! @author Filippo F. Fadda


//! @brief This is the main ElephantOnCouch library namespace.
namespace ElephantOnCouch;


use Rest\Client;
use Rest\Request;
use Rest\Response;
use ElephantOnCouch\Exception\ClientErrorException;
use ElephantOnCouch\Exception\ServerErrorException;
use ElephantOnCouch\Doc\AbstractDoc;
use ElephantOnCouch\Handler\ViewHandler;
use ElephantOnCouch\Attachment;


//! @brief This class is the main class of ElephantOnCouch library. You need an instance of this class to interact with
//! CouchDB.
//! @todo add memcached
//! @nosubgrouping
class ElephantOnCouch extends Client {

  //! @name User Agent
  //! @brief User agent's information.
  // @{
  const USER_AGENT_NAME = "ElephantOnCouch";
  const USER_AGENT_VERSION = "0.1";
  //@}

  //! Default server.
  const DEFAULT_SERVER = "127.0.0.1:5984";

  //! @name Custom Request Header Fields
  // @{
  const DESTINATION_HF = "Destination";
  const X_COUCHDB_WWW_AUTHENTICATE_HF = "X-CouchDB-WWW-Authenticate";
  const X_COUCHDB_FULL_COMMIT_HF = "X-Couch-Full-Commit";
  //@}

  //! @name Custom Request Methods
  // @{
  const COPY_METHOD = "COPY"; // This method is not part of HTTP 1.1 protocol.
  //@}

  //! @name Feeds
  //@{
  const NORMAL_FEED = "normal"; //!< Normal mode.
  const CONTINUOUS_FEED = "continuous"; //!< Continuous (non-polling) mode.
  const LONGPOLL_FEED = "longpoll"; //!< Long polling mode.
  //@}

  //! @name Styles (used in the getDbChanges method)
  //@{
  const MAIN_ONLY_STYLE = "main_only";
  const ALL_DOCS_STYLE = "all_docs";
  //@}

  //! @name Document Paths
  // @{
  const STD_DOC_PATH = "";
  const LOCAL_DOC_PATH = "_local/";
  const DESIGN_DOC_PATH = "_design/";
  //@}

  //! Default CouchDB revisions limit number.
  const REVS_LIMIT = 1000;

  //! Default period after which an empty line is sent during a longpoll or continuous feed.
  const DEFAULT_HEARTBEAT = 60000;

  // Current selected rawencoded database name.
  private $dbName;

  // Used to know if the constructor has been already called.
  private static $initialized = FALSE;


  public function __construct($server = self::DEFAULT_SERVER, $userName = "", $password = "") {
    parent::__construct($server, $userName, $password);

    // We can avoid to call the following code every time a ElephantOnCouch instance is created, testing a static property.
    // Because the static nature of self::$initialized, this code will be executed only one time, even multiple ElephantOnCouch
    // instances are created.
    if (!self::$initialized) {
      self::$initialized = TRUE;

      // CouchDB uses a custom Method.
      Request::addCustomMethod(self::COPY_METHOD);

      // CouchDB uses some custom Header Fields
      Request::addCustomHeaderField(self::DESTINATION_HF);
      Request::addCustomHeaderField(self::X_COUCHDB_WWW_AUTHENTICATE_HF);
      Request::addCustomHeaderField(self::X_COUCHDB_FULL_COMMIT_HF);
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


  //! This method raise an exception when a user provide an invalid database name.
  //! @param[in] string $name Database name.
  //! @exception Exception <c>Message: <i>Invalid database name.</i></c>
  private function validateAndEncodeDbName(&$name) {
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
      throw new \Exception("Invalid database name.");
  }


  //! @brief This method raise an exception when the user provides an unknown document path.
  private function validateDocPath($docPath) {
    if (($docPath != self::STD_DOC_PATH) && ($docPath != self::LOCAL_DOC_PATH) && ($docPath != self::DESIGN_DOC_PATH))
      throw new \Exception("\$docPath is not a valid document type.");
  }


  //! @brief This method raise an exception when the user provides an invalid document identifier.
  //! @exception Exception <c>Message: <i>You must provide a valid \$docId.</i></c>
  private function validateAndEncodeDocId(&$docId) {
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
      throw new \Exception("You must provide a valid \$docId.");
  }


  //! @brief This is a factory method to create a new Request.
  //! @details This method is used to create a Request object. You can still create a Request instance using the appropriate
  //! constructor, but I recommend you to use this factory method, because it does a lot of dirty work. You should use
  //! this method combined with sendRequest.
  private function newRequest($method, $path) {
    $request = new Request($method, $path);
    $request->setHeaderField(Request::ACCEPT_HF, "application/json"); // default accept header value
    $request->setHeaderField(Request::USER_AGENT_HF, self::USER_AGENT_NAME." ".self::USER_AGENT_VERSION);
    return $request;
  }


  protected function handleClientError(Request $request, Response $response) {
    throw new ClientErrorException($request, $response);
  }


  protected function handleServerError(Request $request, Response $response) {
    throw new ServerErrorException($request, $response);
  }


  //! @name Server-level Miscellaneous Methods
  // @{

  //! @brief Creates the admin user.
  // TODO
  public function createAdminUser() {

  }


  //! @brief Restarts the server.
  //! @attention Requires admin privileges.
  //! @bug <a href="https://issues.apache.org/jira/browse/COUCHDB-947" target="_blank">COUCHDB-947</a>
  public function restartServer() {
    $request = $this->newRequest(Request::POST_METHOD, "/_restart");
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    // There is a bug in CouchDB, sometimes it doesn't return the 200 Status Code because it closes the connection
    // before the client has received the entire response. To avoid problems, we trap the exception and we go on.
    try {
      $this->sendRequest($request);
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
  //! use ElephantOnCouch\Client;
  //! use ElephantOnCouch\ResponseException;
  //!
  //! $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, "user", "password");
  //! $couch->selectDb("database");
  //!
  //! try {
  //!   $info = $couch->getSvrInfo();
  //!   print_r($info);
  //! }
  //! catch (Exception $e) {
  //!   if ($e instanceof ResponseException) {
  //!     echo ">>> Code: ".$e->getStatusCode()."\r\n";
  //!     echo ">>> CouchDB Error: ".$e->getError()."\r\n";
  //!     echo ">>> CouchDB Reason: ".$e->getReason()."\r\n";
  //!   }
  //!   else {
  //!     echo "Error code: ".$e->getCode()."\r\n";
  //!     echo "Message: ".$e->getMessage()."\r\n";
  //!   }
  //! }
  //! @endcode
  //! @return an Info object.
  //! @see http://wiki.apache.org/couchdb/HttpGetRoot
  public function getSvrInfo() {
    $response = $this->sendRequest($this->newRequest(Request::GET_METHOD, "/"));
    $info = $response->getBodyAsArray();
    return new SvrInfo($info["couchdb"], $info["version"]);
  }


  //! @brief Returns the favicon.ico file.
  //! @details The favicon is a part of the admin interface, but the handler for it is special as CouchDB tries to make
  //! sure that the favicon is cached for one year. Returns a string that represents the icon.
  //! @return string
  //! @see http://wiki.apache.org/couchdb/HttpGetFavicon
  public function getFavicon() {
    $response = $this->sendRequest($this->newRequest(Request::GET_METHOD, "/favicon.ico"));

    if ($response->getHeaderField(Request::CONTENT_TYPE_HF) == "image/x-icon")
      return $response->getBody();
    else
      throw new \Exception("Content-Type must be image/x-icon.");
  }


  //! @brief Returns server statistics.
  //! @return associative array
  public function getStats() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_stats"))->getBodyAsArray();
  }


  //! @brief Returns a list of all databases on this server.
  //! @return array of string
  //! @see http://wiki.apache.org/couchdb/HttpGetAllDbs
  public function getAllDbs() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_all_dbs"))->getBodyAsArray();
  }


  //! @brief Returns a list of running tasks.
  //! @attention Requires admin privileges.
  //! @return associative array
  //! @exception ResponseException
  //! <c>Code: <i>401 Unauthorized</i></c>\n
  //! <c>Error: <i>unauthorized</i></c>\n
  //! <c>Reason: <i>You are not a server admin.</i></c>
  //! @see http://wiki.apache.org/couchdb/HttpGetActiveTasks
  public function getActiveTasks() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_active_tasks"))->getBodyAsArray();
  }


  //! @brief Returns the tail of the server's log file.
  //! @attention Requires admin privileges.
  //! @param[in] integer $bytes How many bytes to return from the end of the log file.
  //! @return string
  //! @exception ResponseException
  //! <c>Code: <i>401 Unauthorized</i></c>\n
  //! <c>Error: <i>unauthorized</i></c>\n
  //! <c>Reason: <i>You are not a server admin.</i></c>
  //! @see http://wiki.apache.org/couchdb/HttpGetLog
  public function getLogTail($bytes = 1000) {
    $request = $this->newRequest(Request::GET_METHOD, "/_log");
    $request->setQueryParam("bytes", $bytes);
    return $this->sendRequest($request)->getBody();
  }


  //! @brief Returns a list of generated UUIDs.
  //! @param[in] integer $count Requested UUIDs number.
  //! @return string|array If <i>$count = 1</i> (default) returns a string else returns an array of strings.
  //! @see http://wiki.apache.org/couchdb/HttpGetUuids
  public function getUuids($count = 1) {
    if (is_int($count) and ($count > 0)) {
      $request = $this->newRequest(Request::GET_METHOD, "/_uuids");
      $request->setQueryParam("count", $count);

      $response = $this->sendRequest($request);

      if ($count == 1) // We don't need to use === operator because, just above, we made a type checking.
        return $response->getBodyAsArray()['uuids'][0];
      else
        return $response->getBodyAsArray()['uuids'];
    }
    else
      throw new \Exception("\$count must be a positive integer.");
  }

  //@}


  //! @name Server Configuration Methods
  // @{

  //! @brief Returns the entire server configuration or a single section or a single configuration value of a section.
  //! @param[in] string $section Requested section.
  //! @param[in] string $key Requested key.
  //! @return string|array An array with the configuration keys or a simple string in case of a single key.
  public function getConfig($section = "", $key = "") {
    $path = "/_config";

    if (!empty($section)) {
      $path .= "/".$section;

      if (!empty($key))
        $path .= "/".$key;
    }

    return $this->sendRequest($this->newRequest(Request::GET_METHOD, $path))->getBodyAsArray();
  }


  //! @brief Sets a single configuration value in a given section to server configuration.
  //! @param[in] string $section The configuration section.
  //! @param[in] string $key The key.
  //! @param[in] string $key The value for the key.
  public function setConfigKey($section, $key, $value) {
    $request = $this->newRequest(Request::PUT_METHOD, "/_config/".$section."/".$key);
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");
    $request->setBody(json_encode(utf8_encode($value)));
    $this->sendRequest($request);
  }


  //! @brief Deletes a single configuration value from a given section in server configuration.
  //! @param[in] string $section The configuration section.
  //! @param[in] string $key The key.
  public function deleteConfigKey($section, $key) {
    $this->sendRequest($this->newRequest(Request::DELETE_METHOD, "/_config/".$section."/".$key));
  }

  //@}


  //! @name Authentication Methods
  // @{

  //! @brief Returns cookie based login user information.
  //! @return TODO
  public function getSession() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_session"));
  }


  //! @brief Makes cookie based user login.
  //! @return TODO
  public function setSession($userName, $password) {
    $request = $this->newRequest(Request::POST_METHOD, "/_session");

    $request->setHeaderField(self::X_COUCHDB_WWW_AUTHENTICATE_HF, "Cookie");
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/x-www-form-urlencoded");

    $request->setQueryParam("name", $userName);
    $request->setQueryParam("password", $password);

    return $this->sendRequest($request);
  }


  //! @brief Makes user logout.
  //! @return a Response object
  public function deleteSession() {
    return $this->sendRequest($this->newRequest(Request::DELETE_METHOD, "/_session"));
  }


  //! @brief TODO
  public function getAccessToken() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_oauth/access_token"));
  }


  // @brief TODO
  public function getAuthorize() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_oauth/authorize"));
  }


  // @brief TODO
  public function setAuthorize() {
    return $this->sendRequest($this->newRequest(Request::POST_METHOD, "/_oauth/authorize"));
  }


  // @brief TODO
  public function requestToken() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_oauth/request_token"));
  }

  //@}


  //! @name Database-level Miscellaneous Methods
  // @{

  //! @brief Check if a database has been selected. This function is used internally, but you want use it in combination
  //! with exec_request method.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  public function checkForDb() {
    if (empty($this->dbName))
      throw new \Exception("No database selected.");
  }


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
  //! @param[in] string $dbName Database name.
  //! @exception Exception <c>Message: <i>Invalid database name.</i></c>
  public function selectDb($dbName) {
    $this->dbName = $this->validateAndEncodeDbName($dbName);
  }


  //! @brief Creates a new database and selects it.
  //! @param[in] string $dbName The database name. A database must be named with all lowercase letters (a-z),
  //! digits (0-9), or any of the _$()+-/ characters and must end with a slash in the URL. The name has to start with a
  //! lowercase letter (a-z).
  //! @param[in] bool $autoSelect If <b>TRUE</b> selects the created database.
  //! @exception Exception <c>Message: <i>You can't create a database with the same name of the selected database.</i></c>
  //! @exception ResponseException
  //! <c>Code: <i>412 Precondition Failed</i></c>\n
  //! <c>Error: <i>file_exists</i></c>\n
  //! <c>Reason: <i>The database could not be created, the file already exists.</i></c>
  //! @exception ResponseException
  //! <c>Code: <i>400 Bad Request</i></c>\n
  //! <c>Error: <i>illegal_database_name</i></c>\n
  //! <c>Reason: <i>Only lowercase characters (a-z), digits (0-9), and any of the characters _, $, (, ), +, -, and / are allowed. Must begin with a letter.</i></c>\n
  //! @see http://docs.couchone.com/couchdb-api/couchdb-api-db.html#couchdb-api-db_db_put
  //! @bug <a href="https://issues.apache.org/jira/browse/COUCHDB-967" target="_blank">COUCHDB-967</a>
  public function createDb($dbName, $autoSelect = TRUE) {
    if ($dbName != $this->dbName) {
      $this->sendRequest($this->newRequest(Request::PUT_METHOD, "/".rawurlencode($dbName)."/"));

      if ($autoSelect)
        $this->selectDb($dbName);
    }
    else
      throw new \Exception("You can't create a database with the same name of the selected database.");
  }


  //! @brief Deletes an existing database.
  //! @param[in] string $dbName The database name. A database must be named with all lowercase letters (a-z),
  //! digits (0-9), or any of the _$()+-/ characters and must end with a slash in the URL. The name has to start with a
  //! lowercase letter (a-z).
  //! @exception Exception <c>Message: <i>You can't delete the selected database.</i>
  //! @exception ResponseException
  //! <c>Code: <i>404 Not Found</i></c>\n
  //! <c>Error: <i>not_found</i></c>\n
  //! <c>Reason: <i>missing</i></c>
  //! @see http://docs.couchone.com/couchdb-api/couchdb-api-db.html#couchdb-api-db_db_delete
  //! @bug <a href="https://issues.apache.org/jira/browse/COUCHDB-967" target="_blank">COUCHDB-967</a>
  public function deleteDb($dbName) {
    $this->validateAndEncodeDbName($dbName);

    if ($dbName != $this->dbName)
      $this->sendRequest($this->newRequest(Request::DELETE_METHOD, "/".$dbName));
    else
      throw new \Exception("You can't delete the selected database.");
  }


  //! @brief Returns information about the selected database.
  //! @return a DbInfo object
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception ResponseException
  //! <c>Code: <i>404 Not Found</i></c>\n
  //! <c>Error: <i>not_found</i></c>\n
  //! <c>Reason: <i>no_db_file</i></c>
  //! @see http://docs.couchone.com/couchdb-api/couchdb-api-db.html#couchdb-api-db_db_get
  public function getDbInfo() {
    $this->checkForDb();

    return new Dbinfo($this->sendRequest($this->newRequest(Request::GET_METHOD, "/".$this->dbName."/"))->getBodyAsArray());
  }


  //! @brief Obtains a list of the changes made to the database. This can be used to monitor for update and modifications
  //! to the database for post processing or synchronization.
  //! @param[in] integer $heartbeat Period, in milliseconds, after which an empty line is sent during longpoll or
  //! continuous. Must be a positive integer.
  //! @param[in] integer $since Start the results from the specified sequence number.
  //! @param[in] integer $limit Maximum number of rows to return. Must be a positive integer.
  //! @param[in] string $feed Type of feed.
  //! @param[in] integer $heartbeat Period in milliseconds after which an empty line is sent in the results. Only
  //! applicable for <i>longpoll</i> or <i>continuous</i> feeds. Overrides any timeout to keep the feed alive indefinitely.
  //! @param[in] integer $timeout Maximum period to wait before the response is sent. Must be a positive integer.
  //! @param[in] bool $includeDocs If <b>TRUE</b> the Response object includes the changed documents.
  //! @param[in] bool $style Specifies how many revisions are returned in the changes array. The default, <i>main_only</i>,
  //! will only return the winning revision; <i>all_docs</i> will return all the conflicting revisions.
  //! @param[in] string $filter Filter function from a design document to get updates.
  //! @return A Response object.
  //! @todo Exceptions should be documented here.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>\$since must be a non-negative integer.</i></c>
  //! @exception Exception <c>Message: <i>\$limit must be a positive integer.</i></c>
  //! @exception Exception <c>Message: <i>\$feed is not supported.</i></c>
  //! @exception Exception <c>Message: <i>\$heartbeat must be a non-negative integer.</i></c>
  //! @exception Exception <c>Message: <i>\$timeout must be a positive integer.</i></c>
  //! @exception Exception <c>Message: <i>\$style not supported.</i></c>
  //! @see http://wiki.apache.org/couchdb/HTTP_database_API#Changes
  //! @todo This function is not complete.
  public function getDbChanges($since = 0,
                               $limit = 1,
                               $feed = self::NORMAL_FEED,
                               $heartbeat = self::DEFAULT_HEARTBEAT,
                               $timeout = self::DEFAULT_TIMEOUT,
                               $includeDocs = FALSE,
                               $style = self::MAIN_ONLY_STYLE,
                               $filter = "") {
    $this->checkForDb();

    $request = $this->newRequest(Request::GET_METHOD, "/".$this->dbName."/_changes");

    if (is_int($since) and ($since >= 0))
      $request->setQueryParam("since", $since);
    else
      throw new \Exception("\$since must be a non-negative integer.");

    if (is_int($limit) and ($limit > 0))
      $request->setQueryParam("limit", $limit);
    else
      throw new \Exception("\$limit must be a positive integer.");

    if (($feed == self::NORMAL_FEED) or ($feed == self::CONTINUOUS_FEED) or ($feed == self::LONGPOLL_FEED))
      $request->setQueryParam("feed", $feed);
    else
      throw new \Exception("\$feed is not supported.");

    if (($feed == self::CONTINUOUS_FEED) or ($feed == self::LONGPOLL_FEED))
      if (is_int($heartbeat) and ($heartbeat >= 0))
        $request->setQueryParam("heartbeat", $heartbeat);
      else
        throw new \Exception("\$heartbeat must be a non-negative integer.");

    if (is_int($timeout) and ($timeout > 0))
      $request->setQueryParam("timeout", $timeout);
    else
      throw new \Exception("\$timeout must be a positive integer.");

    if ($includeDocs)
      $request->setQueryParam("include_docs", "true");

    if (($style == self::MAIN_ONLY_STYLE) or ($style == self::ALL_DOCS_STYLE))
      $request->setQueryParam("style", $style);
    else
      throw new \Exception("\$style not supported.");

    $request->setQueryParam("filter", $filter);

    return $this->sendRequest($request)->getBodyAsArray();
  }


  //! @brief Starts a compaction for the current selected database.
  //! @details Writes a new version of the database file, removing any unused sections from the new version during write.
  //! Because a new file is temporary created for this purpose, you will need twice the current storage space of the
  //! specified database in order for the compaction routine to complete.
  //! Removes old revisions of documents from the database, up to the per-database limit specified by the <b>_revs_limit</b>
  //! database setting.
  //! Compaction can only be requested on an individual database; you cannot compact all the databases for a CouchDB
  //! instance. The compaction process runs as a background process. You can determine if the compaction process is
  //! operating on a database by obtaining the database meta information, the <b>compact_running</b> value of the returned
  //! database structure will be set to true.
  //! You can also obtain a list of running processes to determine whether compaction is currently running, using the
  //! <i>getActiveTasks</i> method.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception ResponseException
  //! <c>Code: <i>404 Not Found</i></c>\n
  //! <c>Error: <i>not_found</i></c>\n
  //! <c>Reason: <i>no_db_file</i></c>
  //! @attention Requires admin privileges.
  //! @see http://wiki.apache.org/couchdb/Compaction#Database_Compaction
  public function compactDb() {
    $this->checkForDb();

    $request = $this->newRequest(Request::POST_METHOD, "/".$this->dbName."/_compact");

    // A POST method requires Content-Type header.
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    $this->sendRequest($request);
  }


  //! @brief Compacts the specified view.
  //! @details If you have very large views or are tight on space, you might consider compaction as well. To run compact
  //! for a particular view on a particular database, use this method.
  //! @param[in] string $designDocName Name of the design document where is stored the view.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @attention Requires admin privileges.
  //! @see http://wiki.apache.org/couchdb/Compaction#View_compaction
  public function compactView($designDocName) {
    $this->checkForDb();

    $path = "/".$this->dbName."/_compact/".$designDocName;

    $request = $this->newRequest(Request::POST_METHOD, $path);

    // A POST method requires Content-Type header.
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    $this->sendRequest($request);
  }


  //! @brief Removes all outdated view indexes.
  //! @details Old views files remain on disk until you explicitly run cleanup.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @attention Requires admin privileges.
  //! @see http://wiki.apache.org/couchdb/Compaction#View_compaction
  public function cleanupViews() {
    $this->checkForDb();

    $request =  $this->newRequest(Request::POST_METHOD, "/".$this->dbName."/_view_cleanup");

    // A POST method requires Content-Type header.
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    $this->sendRequest($request);
  }


  //! @brief Makes sure all uncommited database changes are written and synchronized to the disk.
  //! @details Default CouchDB configuration use delayed commit to improve performances. So CouchDB allows operations to
  //! be run against the disk without an explicit fsync after each operation. Synchronization takes time (the disk may
  //! have to seek, on some platforms the hard disk cache buffer is flushed, etc.), so requiring an fsync for each update
  //! deeply limits CouchDB's performance for non-bulk writers.
  //! Delayed commit should be left set to true in the configuration settings. Anyway, you can still tell CouchDB to make
  //! an fsync, calling the ensure_full_commit method.
  //! @return string A timestamp when the server instance was started.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception ResponseException
  //! <c>Code: <i>404 Not Found</i></c>\n
  //! <c>Error: <i>not_found</i></c>\n
  //! <c>Reason: <i>no_db_file</i></c>
  //! @see http://docs.couchone.com/couchdb-api/couchdb-api-db.html#couchdb-api-db_db-ensure-full-commit_post
  public function ensureFullCommit() {
    $this->checkForDb();

    $request = $this->newRequest(Request::POST_METHOD, "/".$this->dbName."/_ensure_full_commit");

    // A POST method requires Content-Type header.
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");

    return $this->sendRequest($request)->getBodyAsArray()["instance_start_time"];
  }


  //! @brief Returns the special security object for the database.
  //! @details TODO
  //! @return TODO
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception ResponseException
  //! <c>Code: <i>404 Not Found</i></c>\n
  //! <c>Error: <i>not_found</i></c>\n
  //! <c>Reason: <i>no_db_file</i></c>
  //! @see http://docs.couchone.com/couchdb-api/couchdb-api-db.html#couchdb-api-db_db-security_get
  //! @todo This function is not complete.
  public function getSecurityObj() {
    $this->checkForDb();

    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/".$this->dbName."/_security"));
  }


  //! @brief Sets the special security object for the database.
  //! @details TODO
  //! @return TODO
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception ResponseException
  //! <c>Code: <i>404 Not Found</i></c>\n
  //! <c>Error: <i>not_found</i></c>\n
  //! <c>Reason: <i>no_db_file</i></c>
  //! @see http://docs.couchone.com/couchdb-api/couchdb-api-db.html#couchdb-api-db_db-security_put
  //! @todo This function is not complete.
  public function setSecurityObj() {
    $this->checkForDb();

    return $this->sendRequest($this->newRequest(Request::PUT_METHOD, "/".$this->dbName."/_security"));
  }

  //@}


  //! @name Database Replication Methods
  //! @details The replication is an incremental one way process involving two databases (a source and a destination).
  //! The aim of the replication is that at the end of the process, all active documents on the source database are also
  //! in the destination database and all documents that were deleted in the source databases are also deleted (if
  //! exists) on the destination database.
  //! The replication process only copies the last revision of a document, so all previous revisions that were only on
  //! the source database are not copied to the destination database.
  //! Changes on the master will not automatically replicate to the slaves. To make replication continuous, you must set
  //! <b>\$continuous = TRUE</b>. At this time, CouchDB does not remember continuous replications over a server restart.
  //! Specifying a local source database and a remote target database is called push replication and a remote source and
  //! local target is called pull replication. As of CouchDB 0.9, pull replication is a lot more efficient and resistant
  //! to errors, and it is suggested that you use pull replication in most cases, especially if your documents are large
  //! or you have large attachments.
  // @{

  private function realDbReplication($sourceDbUrl, $targetDbUrl, $createTargetDb = TRUE,
                                     $continuous = FALSE, $filter = NULL, $queryArgs = NULL) {
    // Sets the common parameters.
    if (is_string($sourceDbUrl) && !empty($sourceDbUrl) &&
      is_string($targetDbUrl) && !empty($targetDbUrl)) {
      $body["source"] = $sourceDbUrl;
      $body["target"] = $targetDbUrl;
    }
    else
      throw new \Exception("\$source_db_url and \$target_db_url must be non-empty strings.");

    if (!is_bool($continuous))
      throw new \Exception("\$continuous must be a boolean.");
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
        throw new \Exception("\$createTargetDb must be a boolean.");
      elseif ($createTargetDb)
        $body["create_target"] = $createTargetDb;

      if (!empty($filter)) {
        if (is_string($filter)) // filter option
          $body["filter"] = $filter;
        elseif (is_array($filter)) // doc_ids option
          $body["doc_ids"] = array_values($filter);
        else
          throw new \Exception("\$filter must be a string or an array.");
      }

      // queryParams option
      if (!is_null($queryArgs)) {
        if ($queryArgs instanceof ViewQueryArgs)
          $body["queryParams"] = get_object_vars($queryArgs);
        else
          throw new \Exception("\$queryParams must be an instance of ViewQueryArgs class.");
      }
    }
    elseif ($callerMethod == "cancelDbReplication") {
      $body["cancel"] = TRUE;
    }
    else
      throw new \Exception("realDbReplication can be called only from startDbReplication and cancelDbReplication methods.");

    return $this->sendRequest(Request::POST_METHOD, "/_replicate", NULL, NULL, $body);
  }


  //! @brief Starts replication.
  //! @code start_db_replication("sourcedbname", "http://example.org/targetdbname", TRUE, TRUE); @endcode
  //! @param[in] string $sourceDbUrl TODO
  //! @param[in] string $targetDbUrl
  //! @param[in] boolean $createTargetDb The target database has to exist and is not implicitly created. You can force
  //! the creation setting <b>\$createTargetDb = TRUE</b>.
  //! @param[in] boolean $continuous When you set <b>\$continuous = TRUE</b> CouchDB will not stop after replicating all
  //! missing documents from the source to the target.
  //! At the time of writing, CouchDB doesn't remember continuous replications over a server restart. For the time being,
  //! you are required to trigger them again when you restart CouchDB. In the future, CouchDB will allow you to define
  //! permanent continuous replications that survive a server restart without you having to do anything.
  //! @param[in] string|array $filter TODO
  //! @param[in] QueryArgs $queryArgs TODO
  //! @todo document parameters
  public function startDbReplication($sourceDbUrl, $targetDbUrl, $createTargetDb = TRUE,
                                     $continuous = FALSE, $filter = NULL, $queryArgs = NULL) {
    return $this->realDbReplication($sourceDbUrl, $targetDbUrl, $createTargetDb, $continuous, $filter, $queryArgs);
  }


  //! @brief Cancels replication.
  //! @todo document parameters
  public function cancelDbReplication($sourceDbUrl, $targetDbUrl, $continuous = FALSE) {
    return $this->realDbReplication($sourceDbUrl, $targetDbUrl, $continuous);
  }


  //! @todo this function is not complete
  //! @see http://wiki.apache.org/couchdb/Replication#Replicator_database
  //! @see http://docs.couchbase.org/couchdb-release-1.1/index.html#couchb-release-1.1-replicatordb
  //! @see https://gist.github.com/832610
  public function getReplicator() {

  }

  //@}


  //! @name Query Documents Methods
  // @{

  //! @brief Returns a built-in view of all documents in this database. If keys are specified returns only certain rows.
  //! @todo Add the $keys support
  public function queryAllDocs($keys) {
    $this->checkForDb();

    $request = $this->newRequest(Request::POST_METHOD, "/".$this->dbName."/_all_docs");

    return $this->sendRequest($request);
  }


  //! @brief Executes the given view and returns the result.
  //! @todo document parameters
  public function queryView($designDocName, $viewName, ViewQueryArgs $args = NULL) {
    $this->checkForDb();
    $this->validateAndEncodeDocId($designDocName);
    if (empty($viewName))
      throw new \Exception("You must provide a valid \$viewName.");

    $request = $this->newRequest(Request::GET_METHOD, "/".$this->dbName."/_design/".$designDocName."/_view/".$viewName);
    if (isset($args))
      $request->setMultipleQueryParamsAtOnce($args->asArray());

    return $this->sendRequest($request);
  }


  //! @brief Executes the given view, both map and reduce functions, for all documents and returns the result.
  //! @details Map and Reduce functions are provided by the programmer.
  //! @attention Requires admin privileges.
  //! @todo document parameters
  public function queryTempView($mapFn, $reduceFn = "", ViewQueryArgs $args = NULL) {
    $this->checkForDb();

    $handler = new ViewHandler("temp");
    $handler->mapFn = $mapFn;
    if (!empty($reduce))
      $handler->reduceFn = $reduceFn;

    $request = $this->newRequest(Request::POST_METHOD, "/".$this->dbName."/_temp_view");
    $request->setBody(json_encode($handler->asArray()));
    if (isset($args))
      $request->setMultipleQueryParamsAtOnce($args->asArray());

    return $this->sendRequest($request);
  }

  //@}


  //! @name Revisions Management Methods
  // @{

  //! @brief Given a list of document revisions, returns the document revisions that do not exist in the database.
  public function getMissingRevs() {
    $this->checkForDb();

    $request = $this->newRequest(Request::POST_METHOD, "/".$this->dbName."/_missing_revs");

    return $this->sendRequest($request);
  }


  //! @brief Given a list of document revisions, returns differences between the given revisions and ones that are in
  //! the database.
  public function getRevsDiff() {
    $this->checkForDb();

    $request = $this->newRequest(Request::POST_METHOD, "/".$this->dbName."/_missing_revs");

    return $this->sendRequest($request);
  }


  //! @brief Gets the limit of historical revisions to store for a single document in the database.
  public function getRevsLimit() {
    $this->checkForDb();

    $request = $this->newRequest(Request::GET_METHOD, "/".$this->dbName."/_revs_limit");

    return $this->sendRequest($request);
  }


  //! @brief Sets the limit of historical revisions for a single document in the database.
  //! @param[in] integer $revsLimit (optional) Maximum number historical revisions for a single document in the database.
  //! Must be a positive integer.
  //! @return a Response object
  //! @exception Exception <c>Message: <i>\$revsLimit must be a positive integer.</i></c>
  public function setRevsLimit($revsLimit = self::REVS_LIMIT) {
    $this->checkForDb();

    if (!is_int($revsLimit) or ($revsLimit <= 0))
      throw new \Exception("\$revsLimit must be a positive integer.");

    $request = $this->newRequest(Request::PUT_METHOD, "/".$this->dbName."/_revs_limit");
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");
    $request->setBody(json_encode($revsLimit));

    return $this->sendRequest($request);
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
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>You must provide a valid \$docId.</i></c>
  public function getDocEtag($docId) {
    $this->checkForDb();
    $this->validateAndEncodeDocId($docId);

    $path = "/".$this->dbName."/".$docId;

    $request = $this->newRequest(Request::HEAD_METHOD, $path);

    // CouchDB ETag is included between quotation marks.
    return trim($this->sendRequest($request)->getHeaderField(Response::ETAG_HF), '"');
  }


  //! @brief Returns the latest revision of the document.
  //! @details Since CouchDB uses different paths to store special documents, you must provide the document type for
  //! design and local documents.
  //! @param[in] string $docId The document's identifier.
  //! @param[in] string $docPath The document's type. Allowed values: <i>ElephantOnCouch::STD_DOC</i>, <i>ElephantOnCouch::LOCAL_DOC</i>, <i>ElephantOnCouch::DESIGN_DOC</i>.
  //! @param[in] string $rev (optional) The document's revision.
  //! @param[in] DocOpts $opts Query options to get additional document information, like conflicts, attachments, etc.
  //! @return associative array
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>You must provide a valid \$docId.</i></c>
  public function getDoc($docPath, $docId, $rev = NULL, DocOpts $opts = NULL) {
    $this->checkForDb();
    $this->validateDocPath($docPath);
    $this->validateAndEncodeDocId($docId);

    $path = "/".$this->dbName."/".$docPath.$docId;

    $request = $this->newRequest(Request::GET_METHOD, $path);

    // Retrieves the specific revision of the document.
    if (!empty($rev))
      $request->setQueryParam("rev", (string)$rev);

    // If there are any options, add them to the request.
    if (isset($opts))
      $request->setMultipleQueryParamsAtOnce($opts->asArray());

    $body = $this->sendRequest($request)->getBodyAsArray();

    // We use 'type' metadata to store an instance of a specialized document class. We can have Article and Book classes,
    // both derived from Doc, with special properties and methods. Instead to convert them, we store the class type in a
    // special attribute called <i>AbstractDoc::DOC_CLASS</i> within the others metadata. So, once we retrieve the document,
    // the client creates an instance of the class we provided when we saved the document. We don't need to convert it.
    if (isset($body[AbstractDoc::DOC_CLASS])) { // Special document class inherited from Doc or LocalDoc.
      $type = "\\".$body[AbstractDoc::DOC_CLASS];
      $doc = new $type;
    }
    elseif ($docPath == self::LOCAL_DOC_PATH)   // Local document.
      $doc = new Doc\LocalDoc;
    elseif ($docPath == self::DESIGN_DOC_PATH)  // Design document.
      $doc = new Doc\DesignDoc;
    else                                        // Standard document.
      $doc = new Doc\Doc;

    $doc->assignArray($body);

    return $doc;
  }


  //! @brief Inserts or updates a document into the selected database.
  //! @details Whether the <b>\$doc</b> has an id we use a different HTTP method. Using POST CouchDB generates an id for the doc,
  //! using PUT instead we need to specify one. We can still use the function getUuids() to ask CouchDB for some ids.
  //! This is an internal detail. You have only to know that CouchDB can generate the document id for you.
  //! @param[in] Doc $doc The document you want insert or update.
  //! @param[in] bool $batch_mode You can write documents to the database at a higher rate by using the batch option. This
  //! collects document writes together in memory (on a user-by-user basis) before they are committed to disk.
  //! This increases the risk of the documents not being stored in the event of a failure, since the documents are not
  //! written to disk immediately.
  //! @return A Response object.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception ResponseException
  //! <c>Code: <i>404 Not Found</i></c>\n
  //! <c>Error: <i>not_found</i></c>\n
  //! <c>Reason: <i>no_db_file</i></c>
  //! @exception ResponseException
  //! <c>Code: <i>409 Conflict</i></c>\n
  //! <c>Error: <i>not_found</i></c>\n TODO this is wrong
  //! <c>Reason: <i>no_db_file</i></c> TODO this is wrong
  //! @see http://wiki.apache.org/couchdb/HTTP_Document_API#PUT
  // TODO support the new_edits=true|false option http://wiki.apache.org/couchdb/HTTP_Bulk_Document_API#Posting_Existing_Revisions
  public function saveDoc(AbstractDoc $doc, $batchMode = FALSE) {
    $this->checkForDb();

    // We never use the POST method.
    $method = Request::PUT_METHOD;

    // Whether the document has an id we use a different HTTP method. Using POST CouchDB generates an id for the doc
    // using PUT we need to specify one. We can still use the function getUuids() to ask CouchDB for some ids.
    if (!$doc->issetId())
      $doc->setid(UUID::generate(UUID::UUID_RANDOM, UUID::FMT_STRING));

    // Sets the path according to the document type.
    if ($doc instanceof Doc\DesignDoc)
      $path = "/".$this->dbName."/".self::DESIGN_DOC_PATH.$doc->getId();
    elseif ($doc instanceof Doc\LocalDoc)
      $path = "/".$this->dbName."/".self::LOCAL_DOC_PATH.$doc->getId();
    else
      $path = "/".$this->dbName."/".$doc->getId();

    $request = $this->newRequest($method, $path);
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");
    $request->setBody($doc->asJson());

    // Enables batch mode.
    if ($batchMode)
      $request->setQueryParam("batch", "ok");

    return $this->sendRequest($request);
  }


  //! @brief Deletes the specified document.
  //! @details To delete a document you must provide the document identifier and the revision number.
  //! @param[in] string $docId The document's identifier you want delete.
  //! @param[in] string $rev The document's revision number you want delete.
  //! @param[in] string $docPath The document type. You need to specify a document type only when you want delete a
  //! document. Allowed values: <i>ElephantOnCouch::STD_DOC</i>, <i>ElephantOnCouch::LOCAL_DOC</i>, <i>ElephantOnCouch::DESIGN_DOC</i>.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>You must provide a valid \$docId.</i></c>
  //! @exception Exception <c>Message: <i>\$docPath is not a valid document type.</i></c>
  public function deleteDoc($docPath, $docId, $rev) {
    $this->checkForDb();
    $this->validateDocPath($docPath);
    $this->validateAndEncodeDocId($docId);

    $path = "/".$this->dbName."/".$docPath.rawurlencode($docId);

    $request = $this->newRequest(Request::DELETE_METHOD, $path);
    $request->setQueryParam("rev", (string)$rev);

    // We could use another technique to send the revision number. Here just for documentation.
    // $request->setHeader(Request::IF_MATCH_HEADER, (string)$rev);

    return $this->sendRequest($request);
  }


  //! @brief Makes a duplicate of the specified document. If you want to overwrite an existing document, you need to
  //! specify the target document's revision with a <b>\$rev</b> parameter.
  //! @details If you want copy a special document you must specify his type.
  //! @param[in] string $sourceDocId The source document id.
  //! @param[in] string $targetDocId The destination document id.
  //! @param[in] string $rev Needed when you want override an existent document.
  //! @param[in] string $docPath The document type. Allowed values: <i>ElephantOnCouch::STD_DOC</i>, <i>ElephantOnCouch::LOCAL_DOC</i>, <i>ElephantOnCouch::DESIGN_DOC</i>.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>You must provide a valid \$docId.</i></c>
  //! @exception Exception <c>Message: <i>\$docPath is not a valid document type.</i></c>
  public function copyDoc($docPath, $sourceDocId, $targetDocId, $rev = NULL) {
    $this->checkForDb();
    $this->validateDocPath($docPath);
    $this->validateAndEncodeDocId($sourceDocId);
    $this->validateAndEncodeDocId($targetDocId);

    $path = "/".$this->dbName."/".$docPath.$sourceDocId;

    // This request uses the special method COPY.
    $request = $this->newRequest(self::COPY_METHOD, $path);

    if (empty($rev))
      $request->setHeaderField(self::DESTINATION_HF, $targetDocId);
    else
      $request->setHeaderField(self::DESTINATION_HF, $targetDocId."?rev=".(string)$rev);

    $this->sendRequest($request);
  }


  //! @brief The purge operation removes the references to the deleted documents from the database.
  //! @details A database purge permanently removes the references to deleted documents from the database. Deleting a
  //! document within CouchDB does not actually remove the document from the database, instead, the document is marked as
  //! a deleted (and a new revision is created). This is to ensure that deleted documents are replicated to other
  //! databases as having been deleted. This also means that you can check the status of a document and identify that
  //! the document has been deleted.
  //! The purging of old documents is not replicated to other databases. If you are replicating between databases and
  //! have deleted a large number of documents you should run purge on each database.
  //! Purging documents does not remove the space used by them on disk. To reclaim disk space, you should run compact_db().
  //! @return a Response object
  //! @todo Exceptions should be documented here.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @todo  This function is not complete.
  //! @todo document paremeters
  //! @see http://docs.couchone.com/couchdb-api/couchdb-api-db.html#couchdb-api-db_db-purge_post
  public function purgeDocs(array $docs) {
    $this->checkForDb();

    return $this->sendRequest($this->newRequest(Request::POST_METHOD, "/".$this->dbName));
  }


  //! @brief Inserts, updates and deletes documents in a bulk.
  //! @details Documents that are updated or deleted must contain the 'rev' number. To delete a document, you should set
  //! 'delete = true'.
  //! @todo document parameters
  //! @todo this function is not complete
  public function performBulkOperations(array $docs, $fullCommit = FALSE) {
    $this->checkForDb();

    $path = "/".$this->dbName."/_bulk_docs";

    foreach ($docs as $doc) {
      $request = $this->newRequest(Request::POST_METHOD, $path);

      if ($fullCommit)
        $request->setHeaderField(self::X_COUCHDB_FULL_COMMIT_HF, "full_commit");
      else
        $request->setHeaderField(self::X_COUCHDB_FULL_COMMIT_HF, "delay_commit");

      $this->sendRequest($request);
    }
  }

  //@}


  //! @name Attachments Management Methods
  // @{

  //! @brief Retrieves the attachment from the specified document.
  //! @todo document parameters and exceptions
  public function getAttachment($fileName, $docPath, $docId, $rev = NULL) {
    $this->checkForDb();
    $this->validateDocPath($docPath);
    $this->validateAndEncodeDocId($docId);

    $path = "/".$this->dbName."/".$docPath.$docId."/".$fileName;

    $request = $this->newRequest(Request::GET_METHOD, $path);

    // In case we want retrieve a specific document revision.
    if (!empty($rev))
      $request->setQueryParam("rev", (string)$rev);

    return $this->sendRequest($request)->getBody();
  }


  //! @brief Inserts or updates an attachment to the specified document.
  //! @todo document parameters and exceptions
  public function putAttachment($fileName, $docPath, $docId, $rev = NULL) {
    $this->checkForDb();
    $this->validateDocPath($docPath);
    $this->validateAndEncodeDocId($docId);

    $attachment = Attachment::fromFile($fileName);

    $path = "/".$this->dbName."/".$docPath.$docId."/".rawurlencode($attachment->getName());

    $request = $this->newRequest(Request::PUT_METHOD, $path);
    $request->setHeaderField(Request::CONTENT_LENGTH_HF, $attachment->getContentLength());
    $request->setHeaderField(Request::CONTENT_TYPE_HF, $attachment->getContentType());
    $request->setBody(base64_encode($attachment->getData()));

    // In case of adding or updating an existence document.
    if (!empty($rev))
      $request->setQueryParam("rev", (string)$rev);

    return $this->sendRequest($request);
  }


  //! @brief Deletes an attachment from the document.
  //! @todo document parameters and exceptions
  public function deleteAttachment($fileName, $docPath, $docId, $rev) {
    $this->checkForDb();
    $this->validateDocPath($docPath);
    $this->validateAndEncodeDocId($docId);

    $path = "/".$this->dbName."/".$docPath.$docId."/".rawurlencode($fileName);

    $request = $this->newRequest(Request::DELETE_METHOD, $path);
    $request->setQueryParam("rev", (string)$rev);

    return $this->sendRequest($request);
  }

  //@}


  //! @name Special Design Documents Management Methods
  // @{

  //! @brief Returns basic information about the design document and his views.
  //! @param[in] string $docName The design document's name.
  //! @return associative array
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  public function getDesignDocInfo($docName) {
    $this->checkForDb();
    $this->validateAndEncodeDocId($docName);

    $path = "/".$this->dbName."/".self::DESIGN_DOC_PATH.$docName."/_info";

    $request = $this->newRequest(Request::GET_METHOD, $path);

    return $this->sendRequest($request)->getBodyAsArray();
  }


  // Invokes the show handler without a document
  // /db/_design/design-doc/_show/show-name
  // Invokes the show handler for the given document
  // /db/_design/design-doc/_show/show-name/doc
  // GET /db/_design/examples/_show/posts/somedocid
  // GET /db/_design/examples/_show/people/otherdocid
  // GET /db/_design/examples/_show/people/otherdocid?format=xml&details=true
  // public function showDoc($designDocName, $funcName, $docId, $format, $details = FALSE) {
  //! @todo this function is not complete
  public function showDoc($designDocName, $listName, $docId = NULL) {
  }


  // Invokes the list handler to translate the given view results
  // Invokes the list handler to translate the given view results for certain documents
  // GET /db/_design/examples/_list/index-posts/posts-by-date?descending=true&limit=10
  // GET /db/_design/examples/_list/index-posts/posts-by-tag?key="howto"
  // GET /db/_design/examples/_list/browse-people/people-by-name?startkey=["a"]&limit=10
  // GET /db/_design/examples/_list/index-posts/other_ddoc/posts-by-tag?key="howto"
  // public function listDocs($designDocName, $funcName, $viewName, $queryArgs, $keys = "") {
  //! @todo this function is not complete
  public function listDocs($docId = NULL) {

  }


  // Invokes the update handler without a document
  // /db/_design/design-doc/_update/update-name
  // Invokes the update handler for the given document
  // /db/_design/design-doc/_update/update-name/doc
  //! @todo this function is not complete
  public function callUpdateDocFunc($designDocName, $funcName) {
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