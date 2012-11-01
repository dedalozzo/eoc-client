<?php

//! @file ReplicableDoc.php
//! @brief This file contains the ReplicableDoc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Docs;


//! @brief Standard documents and Design documents are both replicable documents.
//! @nosubgrouping
abstract class ReplicableDoc extends AbstractDoc {

  //! @name Replicable Document Properties
  //@{
  const ATTACHMENTS = "_attachments"; //!< The document attachments.
  const REVS_INFO = "_revs_info";
  const LOCAL_SEQUENCE = "_local_sequence"; //!< TODO
  const CONFLICTS = "_conflicts";
  const DELETED_CONFLICTS = "_deleted_conflicts";
  //@}

  //! If the document has attachments, holds a meta-data structure.
  private $attachments = []; // array of attachments

  //! A list of revisions of the document, and their availability.
  private $revsInfo = [];

  private $conflicts = [];

  private $deletedConflicts = [];


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