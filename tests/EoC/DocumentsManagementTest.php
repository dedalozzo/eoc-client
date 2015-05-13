<?php

/**
 * @file DocumentsManagementTest.php
 * @brief This file contains the DocumentsManagementTest class.
 * @details
 * @author Filippo F. Fadda
 */


error_reporting (E_ALL & ~(E_NOTICE | E_STRICT));

$start = microtime(true);

$loader = require_once __DIR__ . "/../vendor/autoload.php";

use EoC\ElephantOnCouch;
use EoC\ResponseException;
use EoC\DocOpts;
use EoC\Docs\Doc;

const COUCH_USER = "pippo";
const COUCH_PASSWORD = "calippo";
const COUCH_DATABASE = "programmazione";

const USE_CURL = TRUE;
const FIRST_RUN = FALSE;


try {
  $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, "pippo", "calippo");

  if (USE_CURL)
    $couch->useCurl();

  $couch->selectDb("localdb");

  $opts = new DocOpts;
  $opts->includeRevs();
  $opts->includeRevsInfo();
  $opts->includeLatest();
  $opts->includeMeta();
  //$opts->includeOpenRevs();
  $opts->includeLocalSeq();
  $opts->includeConflicts();
  $opts->includeDeletedConflicts();

  $doc = $couch->getDoc(ElephantOnCouch::LOCAL_DOC_PATH, "f73f5082-918a-44d5-dd4d-2d63d22f23e1", NULL, $opts);
  $doc->title = "New title for the document";
  $couch->saveDoc($doc);
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

echo "\r\n\r\nElapsed time: $time";

?>