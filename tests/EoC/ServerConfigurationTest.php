<?php

/**
 * @file ServerConfigurationTest.php
 * @brief This file contains the ServerConfigurationTest class.
 * @details
 * @author Filippo F. Fadda
 */


class ServerConfigurationTest extends PHPUnit_Framework_TestCase {
  public function testGetConfig() {
    print_r($couch->getConfig()); // TEST PASSED!
  }


  public function testGetConfigSection() {
    print_r($couch->getConfig("admins")); // TEST PASSED!
  }


  public function testGetConfigKey() {
    print_r($couch->getConfig("couchdb", "database_dir")); // TEST PASSED!
  }


  public function setConfigKey() {
    $couch->setConfigKey("bunga", "minchiazza", "maronna"); // TEST PASSED!
    $couch->setConfigKey("bunga", "primula", "troia$%£$%&£DAD''"); // TEST PASSED!
  }


  public function deleteConfigKey() {
    $couch->deleteConfigKey("bunga", "minchiazza"); // TEST PASSED!
  }

}
