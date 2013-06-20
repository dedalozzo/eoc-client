<?php

//! @file ReplicableDoc.php
//! @brief This file contains the ReplicableDoc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Doc;


use ElephantOnCouch\Attachment\Attachment;


//! @brief Standard documents and Design documents are both replicable documents.
//! @nosubgrouping
abstract class ReplicableDoc extends AbstractDoc {

  //! @name Replicable Document's Special Attributes
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
    $attachments = [];

    foreach ($this->meta[self::ATTACHMENTS] as $attachment)
      $attachments[] = Attachment::fromArray($attachment);

    return $attachments;
  }


  public function addAttachment(Attachment $attachment) {
    $this->meta[self::ATTACHMENTS][$attachment->getName()] = $attachment->asArray();
  }


  public function removeAttachment($name) {
    if ($this->isMetadataPresent(self::ATTACHMENTS))
      if (array_key_exists($name, $this->meta[self::ATTACHMENTS]))
        unset($this->meta[self::ATTACHMENTS][$name]);
      else
        throw new \Exception("Can't find '$name' attachment in the document.");
    else
      throw new \Exception("The document doesn't have any attachment.");
  }


  public function getLocalSequence() {
    return $this->meta[self::LOCAL_SEQUENCE];
  }


  public function issetLocalSequence() {
    return $this->isMetadataPresent(self::LOCAL_SEQUENCE);
  }

}