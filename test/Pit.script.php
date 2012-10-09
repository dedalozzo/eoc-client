<?php

error_reporting (E_ALL ^ E_NOTICE);

$start = microtime(true);


define('COMPUTER_NAME', 'localhost'); // using a unix socket
//define('COMPUTER_NAME', '127.0.0.1'); // force to using TCP/IP
define('DATABASE_NAME', 'programmazione');
define('LOGIN', 'programmazione');
define('PASSWORD', 'acerola47');

//////////////////////////////////////////////////////////////////////////
// Stereotypes
//////////////////////////////////////////////////////////////////////////

define('ARTICLE_DRAFT', 0); // pink
define('ARTICLE', 2); // no square
define('INFORMATIVE', 1); // white
define('ERROR', 3); // red
define('DOWNLOAD', 133); // red

define('BOOK_DRAFT', 10); // purple
define('BOOK', 11); // no square

define('DISCUSSION_DRAFT', 30); // brown
define('DISCUSSION', 31); // no square



// this function try to make a connection to the database
function dbConnect($computerName, $databaseName, $login, $password) {
  // try to create the connection
  $connection = mysql_connect($computerName, $login, $password) or die(mysql_error());
  // try to connect to the database
  mysql_select_db($databaseName, $connection) or die(mysql_error());

  return $connection;
} // end of dbConnect


require('src/Loader.class.php');

use ElephantOnCouch\Loader;
use ElephantOnCouch\ElephantOnCouch;
use ElephantOnCouch\ResponseException;
use ElephantOnCouch\Doc;

//phpinfo(INFO_GENERAL);

Loader::init();

try {
  //$couch = new Client();
  $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, "pippo", "calippo");

  $couch->useCurl();
  //$couch->useSocket();
  $couch->deleteDb("programmazione2");
  $couch->createDb("programmazione2");
  //$couch->selectDb("programmazione");

  $connection = dbConnect(constant('COMPUTER_NAME'), constant('DATABASE_NAME'), constant('LOGIN'), constant('PASSWORD')); // try to connect to database on Programmazione.it remote Web Server

  $sql = "SELECT idItem, title, body, date, hitNum, replyNum, stereotype, locked, contributorName, correlationCode, idMember FROM Item WHERE ((stereotype = ".constant('ARTICLE').") OR (stereotype = ".constant('BOOK').")) ORDER BY date DESC LIMIT 5";
  $result = mysql_query($sql, $connection) or die(mysql_error());

  while ($item = mysql_fetch_object($result)) {
    $doc = new Doc($item->idItem);
    $item->title = utf8_encode($item->title);
    $item->body = utf8_encode($item->body);
    $doc->assignObject($item);
    $couch->saveDoc($doc);
  }

  mysql_free_result($result);
}
catch (Exception $e) {
  echo ">>> Code: ".$e->getCode()."\r\n";
  echo ">>> Message: ".$e->getMessage()."\r\n";

  if ($e instanceof ResponseException) {
    echo ">>> CouchDB Error: ".$e->getError()."\r\n";
    echo ">>> CouchDB Reason: ".$e->getReason()."\r\n";
  }
  //echo $e->getLine()."\r\n";
  //echo $e->getFile()."\r\n";
  //echo $e->getTraceAsString();
}

$stop = microtime(true);
$time = round($stop - $start, 3);

echo "\r\n\r\n\r\nElapsed time: $time";

?>

