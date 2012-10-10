<?php

//namespace ElephantOnCouch;

$start = microtime(true);

$loader = require_once __DIR__ . "../../vendor/autoload.php";
$loader->add('src\\', __DIR__);

//require('../src/Loader.class.php');

/*function __autoload($className) {
  $className = ltrim($className, '\\');
  $fileName  = '';

  if ($lastNsPos = strripos($className, '\\')) {
      $namespace = substr($className, 0, $lastNsPos);
      $className = substr($className, $lastNsPos + 1);
      $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
  }

  $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.class.php';

  require(__DIR__."/".$fileName);
}*/

//use ElephantOnCouch\Loader;
use ElephantOnCouch\ResponseException;
use ElephantOnCouch\ElephantOnCouch;
use ElephantOnCouch\DocOpts;


//phpinfo(INFO_GENERAL);

//Loader::init();

try {
  Rest\Request::addCustomMethod("__METHOD TEST");
  Rest\Request::addCustomHeaderField("__FIELD TEST");
  $request = new Rest\Request;
  print_r("Nethods: ".count($request->getSupportedMethods())."\n");
  print_r("Header Fields: ".count($request->getSupportedHeaderFields()));


  //$couch = new Client();
  $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, "pippo", "calippo");
  //$couch = new src(src::DEFAULT_SERVER, "", "");

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
  echo $couch->getDocEtag(46959); // TEST PASSED!

  $opts = new DocOpts();
  $opts->includeMeta();
  $opts->includeLatest();
  $opts->includeLocalSeq();
  $opts->includeRevsInfo();
  $opts->includeRevs();
  //$opts->includeOpenRevs();
  var_dump($couch->getDoc(10002, "", "", $opts));
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


echo PHP_EOL.PHP_EOL.json_encode(array("log", "pippo"))."\n";

$line = '["add_fun", "function(doc) { if(doc.score > 50) emit(null, {\'player_name\': doc.name}); }"]';
echo PHP_EOL.PHP_EOL.var_dump(json_decode($line));

?>
 
