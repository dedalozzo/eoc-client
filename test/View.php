<?php

error_reporting (E_ALL & ~(E_NOTICE | E_STRICT));

$start = microtime(true);


//////////////////////////////////////////////////////////////////////////
// Stereotypes
//////////////////////////////////////////////////////////////////////////
//define('ARTICLE_DRAFT', 0); // pink
//define('ARTICLE', 2); // no square
//define('INFORMATIVE', 1); // white
//define('ERROR', 3); // red
//define('DOWNLOAD', 133); // red
//define('BOOK_DRAFT', 10); // purple
//define('BOOK', 11); // no square
//define('DISCUSSION_DRAFT', 30); // brown
//define('DISCUSSION', 31); // no square

//phpinfo(INFO_GENERAL);

$loader = require_once __DIR__ . "/../vendor/autoload.php";

use ElephantOnCouch\ElephantOnCouch;
use ElephantOnCouch\ResponseException;
use ElephantOnCouch\Docs\DesignDoc;
use ElephantOnCouch\Handlers\ViewHandler;

try {
  $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, "pippo", "calippo");

  $couch->useCurl();
  //$couch->useSocket();
  $couch->selectDb("programmazione2");

  // -------------------------------------------------------------------------------------------------------------------
  // FIRST DESIGN DOCUMENT
  // -------------------------------------------------------------------------------------------------------------------
  $doc = DesignDoc::fromArray($couch->getDoc(ElephantOnCouch::DESIGN_DOC, "articles"));
  $doc->resetHandlers();
  //$doc = new DesignDoc("articles");

  // This map function indexes every article stored into the database.
  $closure = "function(\$doc) use (\$emit) {
                if (\$doc->stereotype == 2)
                  \$emit(\$doc->idItem, NULL);
               };";

  $handler = new ViewHandler("articles_by_id");
  $handler->mapFn = stripslashes($closure);
  $doc->addHandler($handler);

  $closure = "function(\$doc) use (\$emit) {
                if (\$doc->contributorName == \"Luca Domenichini\")
                  \$emit(\$doc->contributorName, \$doc->idItem);
               };";

  $handler = new ViewHandler("domenichini");
  $handler->mapFn = stripslashes($closure);
  $doc->addHandler($handler);

  $couch->saveDoc($doc);
  // -------------------------------------------------------------------------------------------------------------------

  // -------------------------------------------------------------------------------------------------------------------
  // SECOND DESIGN DOCUMENT
  // -------------------------------------------------------------------------------------------------------------------
  $doc = DesignDoc::fromArray($couch->getDoc(ElephantOnCouch::DESIGN_DOC, "books"));
  $doc->resetHandlers();
  //$doc = new DesignDoc("books");

  // This map function indexes every book stored into the database.
  $closure = "function(\$doc) use (\$emit) {
                if (\$doc->stereotype == 11)
                  \$emit(\$doc->idItem, NULL);
               };";

  $handler = new ViewHandler("books_by_id");
  $handler->mapFn = stripslashes($closure);
  $doc->addHandler($handler);

  $couch->saveDoc($doc);
  // -------------------------------------------------------------------------------------------------------------------

  $couch->queryView("articles", "articles_by_id");
  $couch->queryView("articles", "domenichini");
  $couch->queryView("books", "books_by_id");
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

echo "\r\n\r\n\r\nElapsed time: $time";

?>

