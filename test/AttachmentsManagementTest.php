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

  //$couch->getAttachment();
  //$couch->putAttachment();
  //$couch->deleteAttachment();
  //$couch->getAttachment();
  //$attachment = \ElephantOnCouch\Attachment::create("/Users/fff/Dropbox/Libri/Programming Language Pragmatics.pdf");
  //$attachment = \ElephantOnCouch\Attachment::fromFile("/Users/fff/Downloads/Dexter.S07E06.720p.HDTV.x264-IMMERSE.srt");
  //$doc->addAttachment($attachment);
  //$couch->putAttachment("/Users/fff/Downloads/boardwalk.empire.s03e08.720p.hdtv.x264-evolve.srt", ElephantOnCouch::DESIGN_DOC_PATH, "books", $doc->rev);
  //$couch->deleteAttachment("boardwalk.empire.s03e08.720p.hdtv.x264-evolve.srt", ElephantOnCouch::DESIGN_DOC_PATH, "books", $doc->rev);
  //$doc->removeAttachment("/Users/fff/Downloads/The.Walking.Dead.S03E04.720p.HDTV.x264-IMMERSE.srt");
  //$doc->removeAttachment("/Users/fff/Downloads/The.Walking.Dead.S03E04.720p.HDTV.x264-IMMERSE.srt");
  //$attachment = \ElephantOnCouch\Attachment::fromFile("/Users/fff/Downloads/The.Walking.Dead.S03E04.720p.HDTV.x264-IMMERSE.srt");
  //$attachment = \ElephantOnCouch\Attachment::fromFile("/Users/fff/Downloads/Dexter.S07E06.720p.HDTV.x264-IMMERSE.srt");
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