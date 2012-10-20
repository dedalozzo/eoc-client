#! /opt/local/bin/php
<?php

//! @file eocsvr.php
//! @author Filippo F. Fadda


//! @brief This class represents the implementation of a Query Server.
//! @details CouchDB delegates computation of Views to external query servers. It communicates with them over standard
//! input/output, using a very simple, line-based protocol. The default query server is written in JavaScript. You can
//! use other languages by setting a MIME type in the language property of a design document or the Content-Type header
//! of a temporary view. Design documents that do not specify a language property are assumed to be of type javascript,
//! as are ad-hoc queries that are POSTed to _temp_view without a Content-Type header.<br />
//! CouchDB launches the query server and starts sending commands. The server responds according to its evaluation
//! of the commands.<br />
//! To use
//! @code
//! [query_servers]
//! php=/usr/bin/eocsvr.php
//! @endcode
//! @warning This class won't work with CGI because uses standard input (STDIN) and standard output (STDOUT).
//! @see http://wiki.apache.org/couchdb/View_server
class ElephantOnCouchServer {
  const TMP_DIR = "/tmp/";
  const LOG_FILENAME = "viewserver.log";

  const EXIT_SUCCESS = 0;
  const EXIT_FAILURE = 1;

  private $funcs;

  private $fd;


  public final function __construct() {
    $this->funcs = [];

    $this->fd = fopen(self::TMP_DIR.self::LOG_FILENAME, "w");
  }


  public final function __destruct() {
    fflush($this->fd);
    fclose($this->fd);
  }


  private static function arrayToObject($array) {
    return is_array($array) ? (object) array_map(__FUNCTION__, $array) : $array;
  }


  public final function run() {
    $this->log("run");

    while ($line = trim(fgets(STDIN))) {
      @list($cmd, $arg) = json_decode($line);

      $this->log($cmd);

      switch ($cmd) {
        case "reset":
          $this->reset();
          break;

        case "add_fun":
          $this->addFun($arg);
          break;

        case "map_doc":
          $this->mapDoc($arg);
          break;

        case "reduce":
          $this->reduce($arg);
          break;

        case "rereduce":
          $this->rereduce($arg);
          break;

        default:
          $this->logError("command_not_supported", "'$cmd' command is not supported by this ViewServer implementation");
          exit(self::EXIT_FAILURE);
          break;
      }

      fflush($this->fd);
    }
  }


  //! TODO
  private final function writeln($str) {
    // CouchDB's message terminator is: \n.
    fputs(STDOUT, $str."\n");
    flush();
  }


  //! @brief Resets the internal state of the view server and makes it forget all previous input.
  //! @details CouchDB calls this function TODO
  private final function reset() {
    unset($this->funcs);
    $this->funcs = [];
    $this->writeln("true");
  }


  //! @brief TODO
  //! @details When creating a view, the view server gets sent the view function for evaluation. The view server should
  //! parse/compile/evaluate the function he receives to make it callable later. If this fails, the view server returns
  //! an error. CouchDB might store several functions before sending in any actual documents.
  private final function addFun($fn) {
    $this->funcs[] = $fn;
    $this->writeln("true");
    //$this->logError("eval_failed", "The function you provided is not a closure");
  }


  //! @brief Maps a document for every single View Function stored.
  //! @details When the view function is stored in the view server, CouchDB starts sending in all the documents in the
  //! database, one at a time. The view server calls the previously stored functions one after another with the document
  //! and stores its result. When all functions have been called, the result is returned as a JSON string.
  private final function mapDoc($doc) {
    $doc = self::arrayToObject($doc);

    // We use a closure here, so we can just expose the emit() function to the closure provided by the user. He will not
    // be able to call sum() or any other helper function, because they are all available as closures. We have also another
    // advantage here: the $map variable is defined inside mapDoc(), so we don't need to declare it as class member.
    $emit = function($key, $value = NULL) use (&$map) {
      $this->log("Key: $key");
      $this->log("Value: $key");
      $map[] = array($key, $value);
    };

    $closure = NULL; // This initialization is made just to prevent a lint error during development.

    $result = []; // Every time we map a document against all the registered functions we must reset the result.

    $this->log("====================================================");
    $this->log("MAP DOC: $doc->title");
    $this->log("====================================================");

    foreach ($this->funcs as $fn) {
      $map = []; // Every time we map a document against a function we must reset the map.

      $this->log("Closure: $fn");

      try {
        // Here we call the closure function stored in the view. The $closure variable contains the function implementation
        // provided by the user. You can have multiple views in a design document and for every single view you can have
        // only one map function.
        // The closure must be declared like:
        //
        //     function($doc) use ($emit) { ... };
        //
        // This technique let you use the syntax '$emit($key, $value);' to emit your record. The function doesn't return
        // any value. You don't need to include any files since the closure's code is executed inside this method.
        eval("\$closure = ".$fn);

        if (is_callable($closure)) {
          call_user_func($closure, $doc);
          $result[] = $map;
          $this->log("Map: ".json_encode($map));
          $this->log("Partial Result: ".json_encode($result));
        }
        else
          $this->logError("call_failed", "The function you provided is not callable");
      }
      catch (Exception $e) {
        $this->logError("php_error", $e->getMessage()."\n".$e->getTraceAsString());
        exit(self::EXIT_FAILURE);
      }

      $this->log("----------------------------------------------------");
    }

    $this->log("Final Result: ".json_encode($result));

    // Sends mappings to CouchDB.
    $this->writeln(json_encode($result));
  }


  private final function reduce() {
    //$this->log("sto chiamando la reduce");
  }


  private final function rereduce() {
    //$this->log("sto chiamando la rereduce");
  }


  public final function sum() {
    //$this->log("sto facendo la somma");
  }


  /*public final function count() {
    //$this->log("sto facendo la somma");
  }*/


  /*public final function stats() {
    //$this->log("sto facendo la somma");
  }*/



  //! @brief Tells CouchDB to append the specified message in the couch.log file.
  //! @details Any message will appear in the couch.log file, as follows:
  //!   [Tue, 22 May 2012 15:26:03 GMT] [info] [<0.80.0>] This is a log message
  //! You can't force the message's level. Every message will be marked as [info] even in case of an error, because
  //! CouchDB doesn't let you specify a different level. In case or error use <i>logError</i> instead.
  //! @warning Keep in mind that you can't use this method inside <i>reset</i> or <i>addFun</>, because you are going to
  //! generate an error. CouchDB in fact doesn't expect a message when it sends <i>reset</i> or <i>add_fun</i> commands.
  //! For debugging purpose you can use the <i>log</i> method, to write messages in a log file of your choice.
  //! @param[in] string $msg The message to store into the log file.
  private final function logMsg($msg) {
    $this->writeln(json_encode(array("log", $msg)));
  }


  //! @brief In case of error CouchDB doesn't take any action. We simply notify the error, sending a special message to it.
  private final function logError($error, $reason) {
    $msg = json_encode(array("error" => $error, "reason" => $reason));
    $this->writeln($msg);
  }


  //! @brief Use this method when you want log something in a log file of your choice.
  private final function log($msg) {
    if (empty($msg))
      fputs($this->fd, "\n");
    else
      fputs($this->fd, date("Y-m-d H:i:s")." - ".$msg."\n");
  }

}


// Creates and starts the server instance.
$svr = new ElephantOnCouchServer();
$svr->run();

?>