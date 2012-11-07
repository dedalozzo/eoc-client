<?php


$start = microtime(true);


$loader = require_once __DIR__ . "/../vendor/autoload.php";

use ElephantOnCouch\ElephantOnCouch;
use ElephantOnCouch\ResponseException;
use ElephantOnCouch\DocOpts;

//phpinfo(INFO_GENERAL);

try {
  Rest\Request::addCustomMethod("__METHOD__TEST");
  Rest\Request::addCustomHeaderField("__FIELD__TEST");
  $request = new Rest\Request;
  print_r("Nethods: ".count($request->getSupportedMethods())."\n");
  print_r("Header Fields: ".count($request->getSupportedHeaderFields())."\n");

  $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, "pippo", "calippo");

  $couch->useCurl();
  //$couch->useSocket();

  $couch->selectDb("programmazione"); // TEST PASSED!

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Server-level miscellaneous methods.
  print_r($couch->getSvrInfo()); // TEST PASSED!
  print_r($couch->getFavicon().PHP_EOL.PHP_EOL); // TEST PASSED!
  print_r($couch->getStats()); // TEST PASSED!
  print_r($couch->getAllDbs()); // TEST PASSED!
  print_r($couch->getActiveTasks()); // TEST PASSED!
  print_r($couch->getLogTail(2000)); // TEST PASSED!
  print_r($couch->getUuids(10)); // TEST PASSED!
  //$couch->restartServer(); // TEST PASSED!

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Server configuration methods.
  print_r($couch->getConfig()); // TEST PASSED!
  print_r($couch->getConfig("admins")); // TEST PASSED!
  print_r($couch->getConfig("couchdb", "database_dir")); // TEST PASSED!
  $couch->setConfigKey("bunga", "minchiazza", "maronna"); // TEST PASSED!
  $couch->setConfigKey("bunga", "primula", "troia$%£$%&£DAD''"); // TEST PASSED!
  $couch->deleteConfigKey("bunga", "minchiazza"); // TEST PASSED!

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Authentication methods.
  //$couch->getSession();
  //$couch->deleteSession();
  //$couch->setSssion("pippo", "calippo");
  //$couch->getAccessToken();
  //$couch->getAuthorize();
  //$couch->setAuthorize();
  //$couch->requestToken();

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Database methods.
  print_r($couch->getDbInfo()); // TEST PASSED!
  $couch->createDb("mazinga", FALSE); // TEST PASSED!
  $couch->deleteDb("mazinga"); // TEST PASSED!
  print_r($couch->getDbChanges()); // TEST PASSED!
  $couch->compactDb(); // TEST PASSED!
  $couch->cleanupViews(); // TEST PASSED!
  echo $couch->ensureFullCommit(); // TEST PASSED!
  //$couch->replicateDb();
  //$couch->getSecurity();
  //$couch->setSecurity();
  //$couch->createAdminUser();

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Database Replication Methods
  //$couch->startReplications());
  //$couch->cancelReplications());
  // TODO add the _replicator APIs

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Query documents methods.
  //$couch->queryView();
  //$couch->queryTempView();

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Revisions Management Methods.
  //$couch->getMissingRevs();
  //$couch->getRevsDiff();
  //$couch->getRevsLimit();
  //$couch->setRevsLimit();

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Documents management methods.
  echo $couch->getDocEtag(48346); // TEST PASSED!

  $opts = new DocOpts();
  $opts->includeMeta();
  $opts->includeLatest();
  $opts->includeLocalSeq();
  $opts->includeRevsInfo();
  $opts->includeRevs();
  //$opts->includeOpenRevs();
  var_dump($couch->getDoc(ElephantOnCouch::STD_DOC_PATH, 48346, "", $opts));
  //$couch->saveDoc();
  //$couch->deleteDoc("10002", "1-40bc3cbd9c712f88542adc935603a4ad");
  //$couch->copyDoc();
  //$couch->purgeDocs();
  //$couch->performBulkOperations();

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Attachments management methods.
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


  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Special design documents management methods.
  //$couch->showDoc();
  //$couch->listDocs();
  //$couch->callUpdateDocFunc();
  //$couch->callRewriteUrlHandler(); NOT IMPLEMENTED!

}
catch (Exception $e) {
  echo ">>> Code: ".$e->getCode().PHP_EOL;
  echo ">>> Message: ".$e->getMessage().PHP_EOL;

  if ($e instanceof ResponseException) {
    echo ">>> CouchDB Error: ".$e->getError().PHP_EOL;
    echo ">>> CouchDB Reason: ".$e->getReason().PHP_EOL;
  }
  //echo $e->getLine()."\r\n";
  //echo $e->getFile()."\r\n";
  //echo $e->getTraceAsString();
}

$stop = microtime(true);
$time = round($stop - $start, 3);

echo PHP_EOL.PHP_EOL."Elapsed time: $time";

?>
 
