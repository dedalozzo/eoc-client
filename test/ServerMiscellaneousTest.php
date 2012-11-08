<?php

error_reporting (E_ALL & ~(E_NOTICE | E_STRICT));

$start = microtime(true);

$loader = require_once __DIR__ . "/../vendor/autoload.php";

use ElephantOnCouch\ElephantOnCouch;
use ElephantOnCouch\ResponseException;

const COUCH_USER = "pippo";
const COUCH_PASSWORD = "calippo";
const COUCH_DATABASE = "programmazione";

const USE_CURL = TRUE;
const FIRST_RUN = FALSE;


try {
  $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, COUCH_USER, COUCH_PASSWORD);

  if (USE_CURL)
    $couch->useCurl();

  $couch->selectDb(COUCH_DATABASE);
  print_r($couch->getSvrInfo());
  print_r($couch->getFavicon().PHP_EOL.PHP_EOL);
  print_r($couch->getStats());
  print_r($couch->getAllDbs());
  print_r($couch->getActiveTasks());
  print_r($couch->getLogTail(2000));
  print_r($couch->getUuids(10));
  //$couch->restartServer();
}
catch (Exception $e) {
  echo ">>> Code: ".$e->getCode()."\r\n";
  echo ">>> Message: ".$e->getMessage()."\r\n";

  if ($e instanceof ResponseException) {
    echo ">>> CouchDB Error: ".$e->getError()."\r\n";
    echo ">>> CouchDB Reason: ".$e->getReason()."\r\n";
  }
}

$stop = microtime(true);
$time = round($stop - $start, 3);

echo "\r\n\r\nElapsed time: $time";

?>