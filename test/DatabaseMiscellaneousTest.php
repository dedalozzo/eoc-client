<?php

//! @file DatabaseMiscellaneousTest.php
//! @brief This file contains the DatabaseMiscellaneousTest class.
//! @details
//! @author Filippo F. Fadda


class DatabaseTest extends PHPUnit_Framework_TestCase {
  public function testCheckForDb() {
  }


  public function testSelectDb() {
  }


  public function testGetDbInfo() {
    print_r($couch->getDbInfo()); // TEST PASSED!
  }


  public function testCreateDb() {
    $couch->createDb("mazinga", FALSE); // TEST PASSED!
  }


  public function testDeleteDb() {
    $couch->deleteDb("mazinga"); // TEST PASSED!
  }


  public function testGetDbChanges() {
    print_r($couch->getDbChanges()); // TEST PASSED!
  }


  public function testCompactDb() {
    $couch->compactDb(); // TEST PASSED!
  }


  public function testCompactView() {
  }


  public function testCleanupViews() {
    $couch->cleanupViews(); // TEST PASSED!
  }


  public function testEnsureFullCommit() {
    echo $couch->ensureFullCommit(); // TEST PASSED!
  }


  public function testGetSecurityObj() {
    //$couch->getSecurity();
  }


  public function testSetSecurityObj() {
    //$couch->setSecurity();
  }

}
