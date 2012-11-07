<?php

//! @file ImportDocsFromMySQLTest.php
//! @brief This file contains the ImportDocsFromMySQLTest class.
//! @details
//! @author Filippo F. Fadda

error_reporting (E_ALL ^ E_NOTICE);

$loader = require_once __DIR__ . "/../vendor/autoload.php";

use ElephantOnCouch\ElephantOnCouch;
use ElephantOnCouch\ResponseException;
use ElephantOnCouch\Docs\Doc;


class Item extends Doc {
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


class ImportDocsFromMySQLTest extends PHPUnit_Framework_TestCase {
  const COMPUTER_NAME = "localhost";
  const DATABASE_NAME = "programmazione";
  const LOGIN = "pippo";
  const PASSWORD = "calippo";

  const LIMIT = " LIMIT 500";

  const ARTICLE_DRAFT = 0;
  const ARTICLE = 2;
  const INFORMATIVE = 1;
  const ERROR = 3;
  const DOWNLOAD = 133;

  const BOOK_DRAFT = 10;
  const BOOK = 11;

  const DISCUSSION_DRAFT = 30;
  const DISCUSSION = 31;

  private $couch;
  private $mysql;


  public function __construct() {
    $this->couch = new ElephantOnCouch(ElephantOnCouch::DEFAULT_SERVER, COUCH_USER, COUCH_PASSWORD);

    if (USE_CURL)
      $this->couch->useCurl();

    $this->mysql = mysql_connect(self::COMPUTER_NAME, self::LOGIN, self::PASSWORD) or die(mysql_error());

    mysql_select_db(self::DATABASE_NAME, $this->mysql) or die(mysql_error());
  }


  public function __destruct() {
    mysql_close($this->mysql);
  }


  public function testImportAllItems() {
    $start = microtime(true);

    try {
      $this->couch->createDb("test_all_documents");

      $sql = "SELECT idItem, title, body, date, hitNum, replyNum, stereotype, locked, contributorName, correlationCode, idMember FROM Item ORDER BY date DESC".self::LIMIT;
      $result = mysql_query($sql, $this->mysql) or die(mysql_error());

      while ($item = mysql_fetch_object($result)) {
        $item->title = utf8_encode($item->title);
        $item->body = utf8_encode($item->body);
        $item->contributorName = utf8_encode($item->contributorName);

        $doc = new Item;
        $doc->assignObject($item);

        $response = $this->couch->saveDoc($doc);

        $this->assertLessThanOrEqual(202, $response->getStatusCode());
      }

      mysql_free_result($result);
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
  }


  public function testImportArticlesAndBooks() {
    $start = microtime(true);

    try {
      $this->couch->createDb("test_articles_and_books");

      $sql = "SELECT idItem, title, body, date, hitNum, replyNum, stereotype, locked, contributorName, correlationCode, idMember FROM Item WHERE ((stereotype = ".self::ARTICLE.") OR (stereotype = ".self::BOOK.")) ORDER BY date DESC".self::LIMIT;
      $result = mysql_query($sql, $this->mysql) or die(mysql_error());

      while ($item = mysql_fetch_object($result)) {
        $item->title = utf8_encode($item->title);
        $item->body = utf8_encode($item->body);
        $item->contributorName = utf8_encode($item->contributorName);

        if ($item->stereotype == self::ARTICLE)
          $doc = new Article;
        else
          $doc = new Book;

        $doc->assignObject($item);

        $response = $this->couch->saveDoc($doc);

        $this->assertLessThanOrEqual(202, $response->getStatusCode());
      }

      mysql_free_result($result);
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
  }


  public function testImportJustTenArticles() {
    $start = microtime(true);

    try {
      $this->couch->createDb("test_just_ten_articles");

      $sql = "SELECT idItem, title, body, date, hitNum, replyNum, stereotype, locked, contributorName, correlationCode, idMember FROM Item WHERE (stereotype = ".self::ARTICLE.") ORDER BY date DESC LIMIT 10";
      $result = mysql_query($sql, $this->mysql) or die(mysql_error());

      while ($item = mysql_fetch_object($result)) {
        $item->title = utf8_encode($item->title);
        $item->body = utf8_encode($item->body);
        $item->contributorName = utf8_encode($item->contributorName);

        $doc = new Article;
        $doc->assignObject($item);

        $response = $this->couch->saveDoc($doc);

        $this->assertLessThanOrEqual(202, $response->getStatusCode());
      }

      mysql_free_result($result);
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
  }

}

