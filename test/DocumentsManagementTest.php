<?php

//! @file DocumentsManagementTest.php
//! @brief This file contains the DocumentsManagementTest class.
//! @details
//! @author Filippo F. Fadda


class DocumentsManagementTest extends PHPUnit_Framework_TestCase {

  public function getDocEtag() {
    echo $couch->getDocEtag(48346); // TEST PASSED!
  }


  public function getDoc() {
    $opts = new DocOpts();
    $opts->includeMeta();
    $opts->includeLatest();
    $opts->includeLocalSeq();
    $opts->includeRevsInfo();
    $opts->includeRevs();
    //$opts->includeOpenRevs();
    var_dump($couch->getDoc(ElephantOnCouch::STD_DOC_PATH, 48346, "", $opts));
    //$couch->saveDoc();
  }


  public function saveDoc() {
  }


  public function deleteDoc() {
    //$couch->deleteDoc("10002", "1-40bc3cbd9c712f88542adc935603a4ad");
  }


  public function copyDoc() {
    //$couch->copyDoc();
  }


  public function purgeDocs() {
    //$couch->purgeDocs();
  }


  public function performBulkOperations() {
    //$couch->performBulkOperations();
  }

}
