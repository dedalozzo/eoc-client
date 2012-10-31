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
use ElephantOnCouch\DocOpts;
use ElephantOnCouch\Docs\LocalDoc;


const FIRST_RUN = FALSE;


class Item extends LocalDoc {
  public function setDate($date) {
    $this->meta["date"] = $date;
  }

  public function getDate() {
    return $this->meta["date"];
  }

  public function setHitNum($hitNum) {
    $this->meta["hitNum"] = $hitNum;
  }

  public function getHitNum() {
    return $this->meta["hitNum"];
  }

  public function setIdMember($idMember) {
    $this->meta["idMember"] = $idMember;
  }

  public function getIdMember() {
    return $this->meta["idMember"];
  }

  public function setIditem($iditem) {
    $this->meta["iditem"] = $iditem;
  }

  public function getIditem() {
    return $this->meta["iditem"];
  }

  public function setReplyNum($replyNum) {
    $this->meta["replyNum"] = $replyNum;
  }

  public function getReplyNum() {
    return $this->meta["replyNum"];
  }

  public function setTitle($title) {
    $this->meta["title"] = $title;
  }

  public function getTitle() {
    return $this->meta["title"];
  }
}


class Article extends Item {
  public function setBody($body) {
    $this->meta["body"] = $body;
  }

  public function getBody() {
    return $this->meta["body"];
  }

  public function setCorrelationCode($correlationCode) {
    $this->meta["correlationCode"] = $correlationCode;
  }

  public function getCorrelationCode() {
    return $this->meta["correlationCode"];
  }
}


class Book extends Article {
  public function setBody($body) {
    $this->meta["body"] = $body;
  }

  public function getBody() {
    return $this->meta["body"];
  }

  public function setPositive($positive) {
    $this->meta["positive"] = $positive;
  }

  public function getPositive() {
    return $this->meta["positive"];
  }

  public function setNegative($negative) {
    $this->meta["negative"] = $negative;
  }

  public function getNegative() {
    return $this->meta["negative"];
  }
}


class MyLocalDoc extends LocalDoc {
  public function setTitle($value) {
    $this->meta["title"] = $value;
  }
}


function arrayToObject($array) {
  return is_array($array) ? (object) array_map(__FUNCTION__, $array) : $array;
}


try {
  $couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, "pippo", "calippo");

  $couch->useCurl();
  //$couch->useSocket();
  $couch->selectDb("localdb");

  $opts = new DocOpts;
  $opts->includeRevs();
  $opts->includeRevsInfo();
  $opts->includeLatest();
  $opts->includeMeta();
  $opts->includeOpenRevs();

  $doc = $couch->getDoc(ElephantOnCouch::LOCAL_DOC_PATH, "b4819145-9a46-4af4-b591-c19817d8e0b1");
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
}

$stop = microtime(true);
$time = round($stop - $start, 3);

echo "\r\n\r\nElapsed time: $time";

?>