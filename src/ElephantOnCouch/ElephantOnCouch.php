<?php

//! @file ElephantOnCouch.class.php
//! @brief This file contains the src class.
//! @details
//! @author Filippo F. Fadda


//! @brief TODO
namespace ElephantOnCouch;


use Rest;
use Rest\Message;
use Rest\Request;
use Rest\Response;


//! @brief This class is the main class of src library. You need an instance of this class to interact with
//! CouchDB.
//! @nosubgrouping
class ElephantOnCouch extends Rest\Client {

  //! @name User Agent
  //! @brief User agent information.
  // @{
  const USER_AGENT_NAME = "src";
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

  //! @name Document Types
  // @{
  const STD_DOC = "";
  const LOCAL_DOC = "_local/";
  const DESIGN_DOC = "_design/";
  //@}

  //! Default CouchDB revisions limit number.
  const REVS_LIMIT = 1000;

  //! Default period after which an empty line is sent during a longpoll or continuous feed.
  const DEFAULT_HEARTBEAT = 60000;

  // Current selected database name.
  private $dbName;


  public function __construct($server = self::DEFAULT_SERVER, $userName = "", $password = "") {
    parent::__construct($server, $userName, $password);

    $this->setUserAgent(self::USER_AGENT_NAME." ".self::USER_AGENT_VERSION);

    // CouchDB uses a custom Method.
    Request::addCustomMethod(self::COPY_METHOD);

    // CouchDB uses some custom Header Fields
    Request::addCustomHeaderField(self::DESTINATION_HF);
    Request::addCustomHeaderField(self::X_COUCHDB_WWW_AUTHENTICATE_HF);
    Request::addCustomHeaderField(self::X_COUCHDB_FULL_COMMIT_HF);
  }


  //! @brief This method raise an exception when the user provides an unknown document type.
  private function checkDocType($docType) {
    if (($docType != self::STD_DOC) && ($docType != self::LOCAL_DOC) && ($docType != self::DESIGN_DOC))
      throw new \Exception("\$docType is not a valid document type.");
  }


  //! @brief This method raise an exception when the user provides an invalid document identificator.
  private function checkDocId($docId) {
    if (empty($docId))
      throw new \Exception("You must provide a valid \$docId.");
  }


  //! @brief This method is used to send a Request to CouchDB. See details for more informations.
  //! @details You can use this method in conjunction with newRequest factory method to build and execute a new request.
  public function sendRequest(Request $request) {
    $response = parent::sendRequest($request);

    if ($response->getStatusCode() >= 400)
      if ($response->hasBody()) {
        $body = $response->getBodyAsArray();
        throw new ResponseException($response->getSupportedStatusCodes()[$response->getStatusCode()], $response->getStatusCode(), $body["error"], $body["reason"]);
      }
      else {
        throw new ResponseException($response->getSupportedStatusCodes()[$response->getStatusCode()], $response->getStatusCode());
      }
    else
      return $response;
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
  //! use src\Client;
  //! use src\ResponseException;
  //!
  //! $couch = new src(src::DEFAULT_SERVER, "user", "password");
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
  //! @return An instance of Info class.
  //! @see http://wiki.apache.org/couchdb/HttpGetRoot
  public function getSvrInfo() {
    $response = $this->sendRequest($this->newRequest(Request::GET_METHOD, "/"));
    $info = $response->getBodyAsArray();
    return new SvrInfo($info["couchdb"], $info["version"]);
  }


  //! @brief Returns the favicon.ico file.
  //! @details The favicon is a part of the admin interface, but the handler for it is special as CouchDB tries to make
  //! sure that the favicon is cached for one year.
  //! @return A string that represents the icon.
  //! @see http://wiki.apache.org/couchdb/HttpGetFavicon
  public function getFavicon() {
    $response = $this->sendRequest($this->newRequest(Request::GET_METHOD, "/favicon.ico"));

    if ($response->getHeaderField(Message::CONTENT_TYPE_HF) == "image/x-icon")
      return $response->getBody();
    else
      throw new \Exception("Content-Type must be image/x-icon.");
  }


  //! @brief Returns server statistics.
  //! @return An associative array.
  public function getStats() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_stats"))->getBodyAsArray();
  }


  //! @brief Returns a list of all databases on this server.
  //! @return An array of string.
  //! @see http://wiki.apache.org/couchdb/HttpGetAllDbs
  public function getAllDbs() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_all_dbs"))->getBodyAsArray();
  }


  //! @brief Returns a list of running tasks.
  //! @attention Requires admin privileges.
  //! @return An associative array.
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
  //! @return A string.
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
  //! @return if <b>$count = 1</b> (default) returns a string else returns an array of strings.
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
  //! @return An array with the configuration keys or a simple string in case of a single key.
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
  public function setConfigKey($section, $key, $value) {
    $request = $this->newRequest(Request::PUT_METHOD, "/_config/".$section."/".$key);
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/json");
    $request->setBody(json_encode(utf8_encode($value)));
    $this->sendRequest($request);
  }


  //! @brief Deletes a single configuration value from a given section in server configuration.
  public function deleteConfigKey($section, $key) {
    $this->sendRequest($this->newRequest(Request::DELETE_METHOD, "/_config/".$section."/".$key));
  }

  //@}


  //! @name Authentication Methods
  // @{

  //! @brief Returns cookie based login user information.
  public function getSession() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_session"));
  }


  //! @brief Makes cookie based user login.
  public function setSession($userName, $password) {
    $request = $this->newRequest(Request::POST_METHOD, "/_session");

    $request->setHeaderField(self::X_COUCHDB_WWW_AUTHENTICATE_HF, "Cookie");
    $request->setHeaderField(Request::CONTENT_TYPE_HF, "application/x-www-form-urlencoded");

    $request->setQueryParam("name", $userName);
    $request->setQueryParam("password", $password);

    return $this->sendRequest($request);
  }


  //! @brief Makes user logout.
  public function deleteSession() {
    return $this->sendRequest($this->newRequest(Request::DELETE_METHOD, "/_session"));
  }


  //! @brief
  public function getAccessToken() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_oauth/access_token"));
  }


  // @brief
  public function getAuthorize() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_oauth/authorize"));
  }


  // @brief
  public function setAuthorize() {
    return $this->sendRequest($this->newRequest(Request::POST_METHOD, "/_oauth/authorize"));
  }


  // @brief
  public function requestToken() {
    return $this->sendRequest($this->newRequest(Request::GET_METHOD, "/_oauth/request_token"));
  }

  //@}


  //! @name Database Methods
  // @{

  //! @brief Check if a database has been selected. This function is used internally, but you want use it in combination
  //! with exec_request method.
  //! @return NULL
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  public function checkForDb() {
    if (empty($this->dbName))
      throw new \Exception("No database selected.");
  }


  //! @brief Sets the database name to use.
  //! @details You should call this method before just after the constructor. CouchDB is a RESTful server implementation,
  //! that means that you can't establish a permanent connection with it, but you just call APIs through HTTP requests.
  //! In every call you have to specify the database name (when a database is required). The src client stores this
  //! information for us, so we don't need to pass the database name as parameter to every method call. The purpose of
  //! this method, is to avoid you repeat database name every time. The function doesn't check if the database really
  //! exists, but it performs a fast check on the name itself. To obtain information about a database, use get_db_info
  //! instead.
  //! @param[in] string $dbName Database name.
  public function selectDb($dbName) {
    // TODO regex on dbName
    $this->dbName = $dbName;
  }


  //! @brief Returns information about the selected database.
  //! @return A DbInfo object.
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


  //! @brief Creates a new database and selects it.
  //! @param[in] string $dbName The database name. A database must be named with all lowercase letters (a-z),
  //! digits (0-9), or any of the _$()+-/ characters and must end with a slash in the URL. The name has to start with a
  //! lowercase letter (a-z).
  //! @param[in] bool $auto_select If <b>TRUE</b> selects the created database.
  //! @return NULL
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
      $this->sendRequest($this->newRequest(Request::PUT_METHOD, "/".$dbName."/"));

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
  //! @return NULL
  //! @exception Exception <c>Message: <i>You can't delete the selected database.</i>
  //! @exception ResponseException
  //! <c>Code: <i>404 Not Found</i></c>\n
  //! <c>Error: <i>not_found</i></c>\n
  //! <c>Reason: <i>missing</i></c>
  //! @see http://docs.couchone.com/couchdb-api/couchdb-api-db.html#couchdb-api-db_db_delete
  //! @bug <a href="https://issues.apache.org/jira/browse/COUCHDB-967" target="_blank">COUCHDB-967</a>
  public function deleteDb($dbName) {
    if ($dbName != $this->dbName)
      $this->sendRequest($this->newRequest(Request::DELETE_METHOD, "/".$dbName));
    else
      throw new \Exception("You can't delete the selected database.");
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
  // TODO Exceptions should be documented here.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>\$since must be a non-negative integer.</i></c>
  //! @exception Exception <c>Message: <i>\$limit must be a positive integer.</i></c>
  //! @exception Exception <c>Message: <i>\$feed is not supported.</i></c>
  //! @exception Exception <c>Message: <i>\$heartbeat must be a non-negative integer.</i></c>
  //! @exception Exception <c>Message: <i>\$timeout must be a positive integer.</i></c>
  //! @exception Exception <c>Message: <i>\$style not supported.</i></c>
  //! @see http://wiki.apache.org/couchdb/HTTP_database_API#Changes
  // TODO This function is not complete.
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
  //! get_active_tasks() method.
  //! @return NULL.
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
  //! @return NULL
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
  //! @return NULL
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
  //! @return A timestamp when the server instance was started.
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
      throw new \Exception("\$source_db_url and \$target_db_url must be strings and can't be empty.");

    if (!is_bool($continuous))
      throw new \Exception("\$continuous must be a boolean.");
    elseif ($continuous)
      $body["continuous"] = $continuous;

    // Uses the specified proxy if any set.
    if (isset($this->proxy))
      $body["proxy"] = $this->proxy;

    // Specific parameters depend by caller method.
    $callerMethod = get_caller_method();

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
  public function startDbReplication($sourceDbUrl, $targetDbUrl, $createTargetDb = TRUE,
                                     $continuous = FALSE, $filter = NULL, $queryArgs = NULL) {
    return $this->realDbReplication($sourceDbUrl, $targetDbUrl, $createTargetDb, $continuous, $filter, $queryArgs);
  }


  //! @brief Cancels replication.
  // TODO
  public function cancelDbReplication($sourceDbUrl, $targetDbUrl, $continuous = FALSE) {
    return $this->realDbReplication($sourceDbUrl, $targetDbUrl, $continuous);
  }


  // TODO
  //! @see http://wiki.apache.org/couchdb/Replication#Replicator_database
  //! @see http://docs.couchbase.org/couchdb-release-1.1/index.html#couchb-release-1.1-replicatordb
  //! @see https://gist.github.com/832610
  public function getReplicator() {

  }

  //@}


  //! @name Query Documents Methods
  // @{

  //! @brief Returns a built-in view of all documents in this database. If keys are specified returns only certain rows.
  // TODO
  public function queryAllDocs($keys) {
    $this->checkForDb();

    $request = $this->newRequest(Request::POST_METHOD, "/".$this->dbName."/_all_docs");

    return $this->sendRequest($request);
  }


  //! @brief TODO
  public function queryView($designDocName, $viewName, ViewQueryArgs $args = NULL) {
    $this->checkForDb();
    $this->checkDocId($designDocName);

    if (empty($viewName))
      throw new \Exception("You must provide a valid \$viewName.");

    $request = $this->newRequest(Request::GET_METHOD, "/".$this->dbName."/_design/".$designDocName."/_view/".$viewName);

    // If there are any options, add them to the request.
    if (isset($args)) {
      $params = $args->asArray();
      foreach ($params as $name => $value)
        $request->setQueryParam($name, $value);
    }

    return $this->sendRequest($request);
  }


  //! @brief Executes a given view function for all documents and return the result.
  //! @details Map and Reduce functions are provided by the programmer.
  //! @attention Requires admin privileges.
  // TODO
  public function queryTempView($mapFn, $reduceFn, ViewQueryArgs $args) {
    $this->checkForDb();

    $request = $this->newRequest(Request::POST_METHOD, "/".$this->dbName."/_temp_view");

    // If there are any options, add them to the request.
    if (isset($args)) {
      $params = $args->asArray();
      foreach ($params as $name => $value)
        $request->setQueryParam($name, $value);
    }

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
  //! @param[in] integer $revsLimit Maximum number historical revisions for a single document in the database.
  //! Must be a positive integer. Optional.
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
  //! @details This function is not available for special documents. To get information about a design document, use
  //! the special function getDesignDocInfo().
  //! @param[in] string $docId The document's identifier.
  //! @return A string.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>You must provide a valid \$docId.</i></c>
  public function getDocEtag($docId) {
    $this->checkForDb();
    $this->checkDocId($docId);

    $path = "/".$this->dbName."/".$docId;

    $request = $this->newRequest(Request::HEAD_METHOD, $path);

    return $this->sendRequest($request)->getHeaderField("Etag");
  }


  //! @brief Returns the latest revision of the document.
  //! @details Since CouchDB uses different paths to store special documents, you must provide the document type for
  //! design and local documents.
  //! @param[in] string $docId The document's identifier.
  //! @param[in] string $docType The document's type. Allowed values: <i>src::STD_DOC</i>, <i>src::LOCAL_DOC</i>, <i>src::DESIGN_DOC</i>.
  //! @param[in] string $rev The document's revision. Optional.
  //! @param[in] DocOpts $opts Query options to get additional document information, like conflicts, attachments, etc.
  //! @return TODO
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>You must provide a valid \$docId.</i></c>
  public function getDoc($docType, $docId, $rev = "", DocOpts $opts = NULL) {
    $this->checkForDb();
    $this->checkDocId($docId);
    $this->checkDocType($docType);

    $path = "/".$this->dbName."/".$docType.$docId;

    $request = $this->newRequest(Request::GET_METHOD, $path);

    // Retrieves the specific revision of the document.
    if (!empty($rev))
      $request->setQueryParam("rev", (string)$rev);

    // If there are any options, add them to the request.
    if (isset($opts)) {
      $params = $opts->asArray();
      foreach ($params as $name => $value)
        $request->setQueryParam($name, $value);
    }

    return $this->sendRequest($request)->getBodyAsArray();
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
    $class = ltrim(get_class($doc), __NAMESPACE__."\\");
    switch ($class) {
      case "LocalDoc":
        $path = "/".$this->dbName."/".self::LOCAL_DOC.$doc->id;
        break;
      case "DesignDoc":
        $path = "/".$this->dbName."/".self::DESIGN_DOC.$doc->id;
        break;
      default:
        $path = "/".$this->dbName."/".$doc->id;
        break;
    }

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
  //! @param[in] string $docType The document type. You need to specify a document type only when you want delete a
  //! document. Allowed values: <i>src::STD_DOC</i>, <i>src::LOCAL_DOC</i>, <i>src::DESIGN_DOC</i>.
  //! @return NULL
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>You must provide a valid \$docId.</i></c>
  //! @exception Exception <c>Message: <i>\$docType is not a valid document type.</i></c>
  public function deleteDoc($docType, $docId, $rev) {
    $this->checkForDb();
    $this->checkDocId($docId);
    $this->checkDocType($docType);

    $path = "/".$this->dbName."/".$docType.$docId;

    $request = $this->newRequest(Request::DELETE_METHOD, $path);
    $request->setQueryParam("rev", $rev);

    // We could use another technique to send the revision number. Here just for documentation.
    // $request->setHeader(Request::IF_MATCH_HEADER, $rev);

    return $this->sendRequest($request);
  }


  //! @brief Makes a duplicate of the specified document. If you want to overwrite an existing document, you need to
  //! specify the target document's revision with a <b>\$rev</b> parameter.
  //! @details If you want copy a special document you must specify his type.
  //! @param[in] string $sourceDocId The source document id.
  //! @param[in] string $targetDocId The destination document id.
  //! @param[in] string $rev Needed when you want override an existent document.
  //! @param[in] string $docType The document type. Allowed values: <i>src::STD_DOC</i>, <i>src::LOCAL_DOC</i>, <i>src::DESIGN_DOC</i>.
  //! @return NULL
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  //! @exception Exception <c>Message: <i>You must provide a valid \$docId.</i></c>
  //! @exception Exception <c>Message: <i>\$docType is not a valid document type.</i></c>
  public function copyDoc($docType, $sourceDocId, $targetDocId, $rev = "") {
    $this->checkForDb();
    $this->checkDocType($docType);
    $this->checkDocId($sourceDocId);
    $this->checkDocId($targetDocId);

    $path = "/".$this->dbName."/".$docType.$sourceDocId;

    // This request uses the special method COPY.
    $request = $this->newRequest(self::COPY_METHOD, $path);

    if (empty($rev))
      $request->setHeaderField(self::DESTINATION_HF, $targetDocId);
    else
      $request->setHeaderField(self::DESTINATION_HF, $targetDocId."?rev=".$rev);

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
  //! @return A Response object.
  // TODO Exceptions should be documented here.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  // TODO This function is not complete.
  //! @see http://docs.couchone.com/couchdb-api/couchdb-api-db.html#couchdb-api-db_db-purge_post
  public function purgeDocs(array $docs) {
    $this->checkForDb();

    return $this->sendRequest($this->newRequest(Request::POST_METHOD, "/".$this->dbName));
  }


  //! @brief Inserts, updates and deletes documents in a bulk.
  //! @details Documents that are updated or deleted must contain the 'rev' number. To delete a document, you should set
  //! 'delete = true'.
  // TODO
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
  // TODO
  public function getAttachment($docId, $fileName, $rev = "") {
    $this->checkForDb();
    $this->checkDocId($docId);

    $path = "/".$this->dbName."/".$docId."/".$fileName;

    $request = $this->newRequest(Request::GET_METHOD, $path);

    return $this->sendRequest($request);
  }


  //! @brief Inserts or updates an attachment to the specified document.
  // TODO
  public function putAttachment($docId, $fileName) {
    $this->checkForDb();
    $this->checkDocId($docId);

    $path = "/".$this->dbName."/".$docId."/".$fileName;

    $request = $this->newRequest(Request::PUT_METHOD, $path);

    return $this->sendRequest($request);
  }


  //! @brief Deletes an attachment from the document.
  // TODO
  public function deleteAttachment($docId, $fileName, $rev) {
    $this->checkForDb();
    $this->checkDocId($docId);

    $path = "/".$this->dbName."/".$docId."/".$fileName;

    $request = $this->newRequest(Request::DELETE_METHOD, $path);

    return $this->sendRequest($request);
  }

  //@}


  //! @name Special Design Documents Management Methods
  // @{

  //! @brief Returns basic information about the design document and his views.
  //! @param[in] string $docName The document's name.
  //! @return An associative array.
  //! @exception Exception <c>Message: <i>No database selected.</i></c>
  public function getDesignDocInfo($docName) {
    $this->checkForDb();
    $this->checkDocId($docName);

    $path = "/".$this->dbName."/".self::DESIGN_DOC.$docName."/_info";

    $request = $this->newRequest(Request::GET_METHOD, $path);

    return $this->sendRequest($request)->getBodyAsArray();
  }


  public function showDoc($designDocName, $listName, $docId = "") {
    // Invokes the show handler without a document
    // /db/_design/design-doc/_show/show-name
    // Invokes the show handler for the given document
    // /db/_design/design-doc/_show/show-name/doc
    // GET /db/_design/examples/_show/posts/somedocid
    // GET /db/_design/examples/_show/people/otherdocid
    // GET /db/_design/examples/_show/people/otherdocid?format=xml&details=true
    // public function showDoc($designDocName, $funcName, $docId, $format, $details = FALSE) {
  }


  // Invokes the list handler to translate the given view results
  // Invokes the list handler to translate the given view results for certain documents
  // GET /db/_design/examples/_list/index-posts/posts-by-date?descending=true&limit=10
  // GET /db/_design/examples/_list/index-posts/posts-by-tag?key="howto"
  // GET /db/_design/examples/_list/browse-people/people-by-name?startkey=["a"]&limit=10
  // GET /db/_design/examples/_list/index-posts/other_ddoc/posts-by-tag?key="howto"
  // public function listDocs($designDocName, $funcName, $viewName, $queryArgs, $keys = "") {
  public function listDocs($docId = "") {

  }


  // Invokes the update handler without a document
  // /db/_design/design-doc/_update/update-name
  // Invokes the update handler for the given document
  // /db/_design/design-doc/_update/update-name/doc
  public function callUpdateDocFunc($designDocName, $funcName) {
    // a PUT request against the handler function with a document id: /<database>/_design/<design>/_update/<function>/<docid>
    // a POST request against the handler function without a document id: /<database>/_design/<design>/_update/<function>
  }


  // Invokes the URL rewrite handler and processes the request after rewriting
  // THIS FUNCTION MAKE NO SENSE
  //public function rewriteUrl($designDocName) {
    // /db/_design/design-doc/_rewrite
  //}

  //@}

}
