<?php

/**
 * @file ServerMiscellaneousTest.php
 * @brief This file contains the ServerMiscellaneousTest class.
 * @details
 * @author Filippo F. Fadda
 */


class ServerMiscellaneousTest extends PHPUnit_Framework_TestCase {
  public function testCreateAdminUser() {
    //$couch->createAdminUser();
  }


  public function testRestartServer() {
    //$couch->restartServer(); // TEST PASSED!
  }


  public function testGetSvrInfo() {
    print_r($couch->getSvrInfo()); // TEST PASSED!
  }


  public function testGetFavicon() {
    print_r($couch->getFavicon().PHP_EOL.PHP_EOL); // TEST PASSED!
  }


  public function testGetStats() {
    print_r($couch->getStats()); // TEST PASSED!
  }


  public function testGetAllDbs() {
    print_r($couch->getAllDbs()); // TEST PASSED!
  }


  public function testGetActiveTasks() {
    print_r($couch->getActiveTasks()); // TEST PASSED!
  }


  public function testGetLogTail() {
    print_r($couch->getLogTail(2000)); // TEST PASSED!
  }


  public function testGetUuids() {
    print_r($couch->getUuids(10)); // TEST PASSED!
  }

}
