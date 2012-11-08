<?php

error_reporting (E_ALL & ~(E_NOTICE | E_STRICT));

$start = microtime(true);

$loader = require_once __DIR__ . "/../vendor/autoload.php";

use ElephantOnCouch\ElephantOnCouch;
use ElephantOnCouch\ResponseException;
use ElephantOnCouch\Attachment;

const COUCH_USER = "pippo";
const COUCH_PASSWORD = "calippo";
const COUCH_DATABASE = "programmazione";

const ATTACHMENTS_DIR = "/Users/fff/Downloads/Attachments/";

const DOC_ID = "000569be-ecd9-446f-f681-74224e3dfc8b";

const USE_CURL = FALSE;
const FIRST_RUN = FALSE;


try {
  $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, COUCH_USER, COUCH_PASSWORD);

  if (USE_CURL)
    $couch->useCurl();

  $couch->selectDb(COUCH_DATABASE);

  /*$couch->putAttachment(ATTACHMENTS_DIR."Is your API naked?.pdf", ElephantOnCouch::STD_DOC_PATH, DOC_ID, DOC_REV);
  $couch->putAttachment(ATTACHMENTS_DIR."J2EE versus .NET.pdf", ElephantOnCouch::STD_DOC_PATH, DOC_ID, DOC_REV);
  $couch->putAttachment(ATTACHMENTS_DIR."Joe Vitale - Greatest Money Making Secret In History.pdf", ElephantOnCouch::STD_DOC_PATH, DOC_ID, DOC_REV);
  $couch->putAttachment(ATTACHMENTS_DIR."L'auto ad aria compressa.pdf", ElephantOnCouch::STD_DOC_PATH, DOC_ID, DOC_REV);
  $couch->putAttachment(ATTACHMENTS_DIR."La memoria del futuro.pdf", ElephantOnCouch::STD_DOC_PATH, DOC_ID, DOC_REV);
  $couch->putAttachment(ATTACHMENTS_DIR."LetteraSullaFelicità.doc", ElephantOnCouch::STD_DOC_PATH, DOC_ID, DOC_REV);*/
  $couch->deleteAttachment("pippo?.txt", ElephantOnCouch::STD_DOC_PATH, DOC_ID, $couch->getDocEtag(DOC_ID));
  $couch->putAttachment(ATTACHMENTS_DIR."pippo?.txt", ElephantOnCouch::STD_DOC_PATH, DOC_ID, $couch->getDocEtag(DOC_ID));

  /*$attachment = Attachment::fromFile(ATTACHMENTS_DIR."Is your API naked?.pdf");
  $attachment = Attachment::fromFile(ATTACHMENTS_DIR."J2EE versus .NET.pdf");
  $attachment = Attachment::fromFile(ATTACHMENTS_DIR."Joe Vitale - Greatest Money Making Secret In History.pdf");
  $attachment = Attachment::fromFile(ATTACHMENTS_DIR."L'auto ad aria compressa.pdf");
  $attachment = Attachment::fromFile(ATTACHMENTS_DIR."La memoria del futuro.pdf");
  $attachment = Attachment::fromFile(ATTACHMENTS_DIR."LetteraSullaFelicità.doc");
  $attachment = Attachment::fromFile(ATTACHMENTS_DIR."pippo.txt");*/

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