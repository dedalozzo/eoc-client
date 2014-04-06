<?php

error_reporting (E_ALL & ~(E_NOTICE | E_STRICT));

$start = microtime(true);

$loader = require_once __DIR__ . "/../vendor/autoload.php";

use ElephantOnCouch\Couch;
use ElephantOnCouch\Doc\DesignDoc;
use ElephantOnCouch\Handler\ViewHandler;
use ElephantOnCouch\Opt\ViewQueryOpts;

const COUCH_USER = "pippo";
const COUCH_PASSWORD = "calippo";
const COUCH_DATABASE = "elephantoncouch_test";

const USE_CURL = FALSE;
const FIRST_RUN = FALSE;


try {
  if (USE_CURL)
    Couch::useCurl();

  $couch = new Couch(Couch::DEFAULT_SERVER, COUCH_USER, COUCH_PASSWORD);

  $couch->selectDb(COUCH_DATABASE);

  // ===================================================================================================================
  // FIRST DESIGN DOCUMENT
  // ===================================================================================================================
  if (FIRST_RUN)
    $doc = DesignDoc::create("articles");
  else {
    $doc = $couch->getDoc(Couch::DESIGN_DOC_PATH, "articles");
    $doc->resetHandlers();
  }

  // -------------------------------------------------------------------------------------------------------------------
  // FIRST HANDLER
  // -------------------------------------------------------------------------------------------------------------------
  $map = "function(\$doc) use (\$emit) {
            if (\$doc->stereotype == 2)
              \$emit(\$doc->idItem, NULL);
          };";

  $reduce = "function(\$keys, \$values, \$rereduce) {
               if (\$rereduce)
                 return array_sum(\$values);
               else
                 return sizeof(\$values);
             };";

  $handler = new ViewHandler("articles_by_id");
  $handler->mapFn = $map;
  //$handler->reduceFn = $reduce;
  //$handler->useBuiltInReduceFnCount();
  $doc->addHandler($handler);

  // -------------------------------------------------------------------------------------------------------------------
  // SECOND HANDLER
  // -------------------------------------------------------------------------------------------------------------------
  $map = "function(\$doc) use (\$emit) {
                if (\$doc->contributorName == \"Andrea Chiarelli\")
                  \$emit(\$doc->contributorName, \$doc->idItem);
               };";

  $handler = new ViewHandler("chiarelli");
  $handler->mapFn = $map;
  $handler->useBuiltInReduceFnCount();
  $doc->addHandler($handler);

  // -------------------------------------------------------------------------------------------------------------------
  // THIRD HANDLER
  // -------------------------------------------------------------------------------------------------------------------
  $map = "function(\$doc) use (\$emit) {
            \$emit(\$doc->stereotype);
          };";

  $reduce = "function(\$keys, \$values, \$rereduce) {
               return sizeof(\$values);
             };";

  $handler = new ViewHandler("items_by_stereotype");
  $handler->mapFn = $map;
  $handler->reduceFn = $reduce;
  //$handler->useBuiltInReduceFnCount();
  $doc->addHandler($handler);

  // Saves the document.
  $couch->saveDoc($doc);

  // ===================================================================================================================
  // SECOND DESIGN DOCUMENT
  // ===================================================================================================================
  if (FIRST_RUN)
    $doc = DesignDoc::create("books");
  else {
    $doc = $couch->getDoc(Couch::DESIGN_DOC_PATH, "books");
    $doc->resetHandlers();
  }

  // -------------------------------------------------------------------------------------------------------------------
  // FIRST HANDLER
  // -------------------------------------------------------------------------------------------------------------------
  $map = "function(\$doc) use (\$emit) {
                if (\$doc->stereotype == 11)
                  \$emit(\$doc->idItem, NULL);
               };";

  $handler = new ViewHandler("books");
  $handler->mapFn = $map;
  //$handler->reduceFn = $reduce;
  $handler->useBuiltInReduceFnCount();
  $doc->addHandler($handler);

  // -------------------------------------------------------------------------------------------------------------------
  // SECOND HANDLER
  // -------------------------------------------------------------------------------------------------------------------
  $map = "function(\$doc) use (\$emit) {
                if (\$doc->stereotype == 1)
                  \$emit(\$doc->idItem, NULL);
               };";

  $handler = new ViewHandler("draft_books");
  $handler->mapFn = $map;
  //$handler->reduceFn = $reduce;
  $handler->useBuiltInReduceFnCount();
  $doc->addHandler($handler);

  // Saves the document.
  $couch->saveDoc($doc);

  // ===================================================================================================================
  // QUERY THE VIEWS
  // ===================================================================================================================
  //echo $couch->queryAllDocs();
  //echo $couch->queryView("articles", "articles_by_id");
  //$opts = new ViewQueryOpts();
  //$opts->doNotReduce();
  //$opts->includeDocs();
  //echo $couch->queryView("articles", "chiarelli", NULL, $opts);
  //echo $couch->queryView("books", "books");
  //echo $couch->queryView("books", "draft_books");

  $opts = new ViewQueryOpts();
  $opts->groupResults();
  echo $couch->queryView("articles", "items_by_stereotype", NULL, $opts);
}
catch (Exception $e) {
  echo $e;
}

$stop = microtime(true);
$time = round($stop - $start, 3);

echo "\r\n\r\nElapsed time: $time";


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