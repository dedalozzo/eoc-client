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

  print_r($couch->getDbInfo()); // TEST PASSED!
  $couch->createDb("mazinga", FALSE); // TEST PASSED!
  $couch->createDb("asdadaaandler767656/$4d()dfsfs____d____a-", FALSE);
  $couch->deleteDb("mazinga"); // TEST PASSED!
  $couch->deleteDb("asdadaaandler767656/$4d()dfsfs____d____a-");
  print_r($couch->getDbChanges()); // TEST PASSED!
  $couch->compactDb(); // TEST PASSED!
  $couch->cleanupViews(); // TEST PASSED!
  echo $couch->ensureFullCommit(); // TEST PASSED!
  //$couch->replicateDb();
  //$couch->getSecurity();
  //$couch->setSecurity();
  //$couch->createAdminUser();
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