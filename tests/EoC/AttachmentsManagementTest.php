<?php

/**
 * @file AttachmentsManagementTest.php
 * @brief This file contains the AttachmentsManagementTest class.
 * @details
 * @author Filippo F. Fadda
 */


class AttachmentsManagementTest extends PHPUnit_Framework_TestCase {

  public function testGetAttachment() {
    //$couch->getAttachment();
    /*
    $couch->deleteAttachment("pippo?.txt", ElephantOnCouch::STD_DOC_PATH, DOC_ID, $couch->getDocEtag(DOC_ID));

    //$couch->deleteDb("adajhdgas");
    //$couch->deleteDoc(ElephantOnCouch::STD_DOC_PATH, DOC_ID, "hhgg");

    //$couch->putAttachment(ATTACHMENTS_DIR."pippo?.txt", ElephantOnCouch::STD_DOC_PATH, DOC_ID, $couch->getDocEtag(DOC_ID));

    /*$attachment = Attachment::fromFile(ATTACHMENTS_DIR."Is your API naked?.pdf");
    $attachment = Attachment::fromFile(ATTACHMENTS_DIR."J2EE versus .NET.pdf");
    $attachment = Attachment::fromFile(ATTACHMENTS_DIR."Joe Vitale - Greatest Money Making Secret In History.pdf");
    $attachment = Attachment::fromFile(ATTACHMENTS_DIR."L'auto ad aria compressa.pdf");
    $attachment = Attachment::fromFile(ATTACHMENTS_DIR."La memoria del futuro.pdf");
    $attachment = Attachment::fromFile(ATTACHMENTS_DIR."LetteraSullaFelicità.doc");
    $attachment = Attachment::fromFile(ATTACHMENTS_DIR."pippo.txt");*/

    //$doc->addAttachment($attachment);
    //$couch->putAttachment("/Users/fff/Downloads/boardwalk.empire.s03e08.720p.hdtv.x264-evolve.srt", ElephantOnCouch::DESIGN_DOC_PATH, "books", $doc->rev);
    //
    //
    //$attachment = \ElephantOnCouch\Attachment::fromFile("/Users/fff/Downloads/The.Walking.Dead.S03E04.720p.HDTV.x264-IMMERSE.srt");
    //$attachment = \ElephantOnCouch\Attachment::fromFile("/Users/fff/Downloads/Dexter.S07E06.720p.HDTV.x264-IMMERSE.srt");
    
  }


  public function testPutAttachment() {
    $couch->putAttachment(ATTACHMENTS_DIR."Is your API naked?.pdf", Couch::STD_DOC_PATH, DOC_ID, DOC_REV);
    $couch->putAttachment(ATTACHMENTS_DIR."J2EE versus .NET.pdf", Couch::STD_DOC_PATH, DOC_ID, DOC_REV);
    $couch->putAttachment(ATTACHMENTS_DIR."Joe Vitale - Greatest Money Making Secret In History.pdf", Couch::STD_DOC_PATH, DOC_ID, DOC_REV);
    $couch->putAttachment(ATTACHMENTS_DIR."L'auto ad aria compressa.pdf", Couch::STD_DOC_PATH, DOC_ID, DOC_REV);
    $couch->putAttachment(ATTACHMENTS_DIR."La memoria del futuro.pdf", Couch::STD_DOC_PATH, DOC_ID, DOC_REV);
    $couch->putAttachment(ATTACHMENTS_DIR."LetteraSullaFelicità.doc", Couch::STD_DOC_PATH, DOC_ID, DOC_REV);*/
  }


  public function testDeleteAttachment() {
    $couch->deleteAttachment("boardwalk.empire.s03e08.720p.hdtv.x264-evolve.srt", ElephantOnCouch::DESIGN_DOC_PATH, "books", $doc->rev);

    $doc->removeAttachment("/Users/fff/Downloads/The.Walking.Dead.S03E04.720p.HDTV.x264-IMMERSE.srt");
    $doc->removeAttachment("/Users/fff/Downloads/The.Walking.Dead.S03E04.720p.HDTV.x264-IMMERSE.srt");
  }

}
