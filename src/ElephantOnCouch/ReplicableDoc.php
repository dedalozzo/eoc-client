<?php

//! @file ReplicableDoc.php
//! @brief This file contains the ReplicableDoc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief TODO
//! @nosubgrouping
abstract class ReplicableDoc extends AbstractDoc {

  //! If the document has attachments, holds a meta-data structure
  private $attachments = array(); // array of attachments

  //! Revision history of the document.
  private $revisions = array();

  //! A list of revisions of the document, and their availability.
  private $revsInfo = array();


  private $conflicts = array();

  private $deletedConflicts = array();


  public function getAttachments() {
    return $this->attachments;
  }


  public function addAttachment($attachment) {
    $this->attachments = $attachment;
  }


  public function removeAttachment($attachment) {
    $this->attachments = $attachment;
  }

}

?>