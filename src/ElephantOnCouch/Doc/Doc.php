<?php

/**
 * @file Doc.php
 * @brief This file contains the Doc class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Doc;


use ElephantOnCouch\Doc\Attachment\Attachment;


/**
 * @brief Standard documents are replicable documents.
 * @nosubgrouping
 */
class Doc extends AbstractDoc {
  const SEPARATOR = '::'; //!< Used to separate the ID from the version number.

  const ATTACHMENTS = "_attachments";
  const REVS_INFO = "_revs_info";
  const CONFLICTS = "_conflicts";
  const DELETED_CONFLICTS = "_deleted_conflicts";

  //! If the document has attachments, holds a meta-data structure.
  private $attachments = []; // array of attachments

  //! A list of revisions of the document, and their availability.
  private $revsInfo = [];

  private $conflicts = [];

  private $deletedConflicts = [];


  protected function fixDocId() {}


  /**
   * @brief Prunes the ID of its version number, if any.
   * @return string
   */
  public function getUnversionId() {
    return strtok($this->meta['_id'], self::SEPARATOR);
  }


  /**
   * @brief Standard documents path is null.
   * @return string An empty string.
   */
  public function getPath() {
    return "";
  }


  /**
   * @brief
   * @todo To be documented.
   */
  public function getAttachments() {
    $attachments = [];

    foreach ($this->meta[self::ATTACHMENTS] as $attachment)
      $attachments[] = Attachment::fromArray($attachment);

    return $attachments;
  }


  /**
   * @brief
   * @todo To be documented.
   */
  public function addAttachment(Attachment $attachment) {
    $this->meta[self::ATTACHMENTS][$attachment->getName()] = $attachment->asArray();
  }


  /**
   * @brief
   * @todo To be documented.
   */
  public function removeAttachment($name) {
    if ($this->isMetadataPresent(self::ATTACHMENTS))
      if (array_key_exists($name, $this->meta[self::ATTACHMENTS]))
        unset($this->meta[self::ATTACHMENTS][$name]);
      else
        throw new \Exception("Can't find '$name' attachment in the document.");
    else
      throw new \Exception("The document doesn't have any attachment.");
  }


  /**
   * @brief
   * @todo To be documented.
   */
  public function getLocalSequence() {
    return $this->meta['_local_sequence'];
  }


  /**
   * @brief
   * @todo To be documented.
   */
  public function issetLocalSequence() {
    return $this->isMetadataPresent('_local_sequence');
  }

}