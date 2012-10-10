<?php

error_reporting (E_ALL ^ E_NOTICE);

$start = microtime(true);


//////////////////////////////////////////////////////////////////////////
// DEBUG shit
//////////////////////////////////////////////////////////////////////////
/*    if (!is_null($body) && !is_associative_array($body))
throw new \Exception("\$body must be an associative array.");
else
$this->body = $body;*/


/*$info = new ReflectionFunction($closure);

$this->log("Function name: ".$info->getName());

if ($info->isClosure())
  $this->log("It's a closure!");
else
  $this->log("It isn't is a closure!");

if (is_callable($closure))
  $this->log("It's callable!");
else
  $this->log("It's not callable!");

$this->log($info);
foreach( $info->getParameters() as $param ) {
  $this->log($param);
}*/

/*$this->log("1° step");

$closure = function($doc) use ($emit) {
  $this->log("3° step");

  if ($doc->stereotype == 2) {
    $emit($doc->idItem, NULL);
    $this->log("idItem: ".$doc->idItem);
    $this->log("Stereotype: ".$doc->stereotype);
  }
};

$this->log("2° step");*/


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


require('../src/Loader.class.php');

use ElephantOnCouch\Loader;
use ElephantOnCouch\ElephantOnCouch;
use ElephantOnCouch\ResponseException;
use ElephantOnCouch\DesignDoc;
use ElephantOnCouch\ViewsHandler;

//phpinfo(INFO_GENERAL);

Loader::init();

try {
  $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, "pippo", "calippo");

  $couch->useCurl();
  //$couch->useSocket();
  $couch->selectDb("programmazione2");

  // This map function indexes every article stored into the database.
  $closure1 = "function(\$doc) use (\$emit) {
                if (\$doc->stereotype == 2)
                  \$emit(\$doc->idItem, NULL);
               };";


  // This map function indexes every book stored into the database.
  $closure2 = "function(\$doc) use (\$emit) {
                if (\$doc->stereotype == 11)
                  \$emit(\$doc->idItem, NULL);
               };";

  $doc = DesignDoc::fromArray($couch->getDoc(ElephantOnCouch::DESIGN_DOC, "items"));
  $doc->resetHandlers();
  //$doc = new DesignDoc("items");

  /*$handler1 = new ViewsHandler("articles_by_id");
  $handler1->mapFn = stripslashes($closure1);
  $doc->addHandler($handler1);*/

  /*$handler2 = new ViewsHandler("books_by_id");
  $handler2->mapFn = stripslashes($closure2);
  $doc->addHandler($handler2);*/

  $couch->saveDoc($doc);

  //print_r($doc->asJson());

  print_r($couch->queryView("items", "articles_by_id"));
  //print_r($couch->queryView("items", "books_by_id"));

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

