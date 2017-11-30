<?php

/**
 * @file Doc.php
 * @brief This file contains the Doc class.
 * @details
 * @author Filippo F. Fadda
 */


namespace EoC\Doc;


use EoC\Doc\Attachment\Attachment;


/**
 * @brief Standard documents are replicable documents.
 * @nosubgrouping
 */
class Doc extends AbstractDoc {
  const ATTACHMENTS = "_attachments";
  const REVS_INFO = "_revs_info";
  const CONFLICTS = "_conflicts";
  const DELETED_CONFLICTS = "_deleted_conflicts";
  const LOCAL_SEQUENCE = "_local_seq";


  protected function fixDocId() {}


  /**
   * @brief Standard documents path is null.
   * @retval string An empty string.
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
   * @brief Adds an attachment.
   */
  public function addAttachment(Attachment $attachment) {
    $this->meta[self::ATTACHMENTS][$attachment->getName()] = $attachment->asArray();
  }


  /**
   * @brief Removes an attachment.
   */
  public function removeAttachment($name) {
    if ($this->isMetadataPresent(self::ATTACHMENTS))
      if (array_key_exists($name, $this->meta[self::ATTACHMENTS]))
        unset($this->meta[self::ATTACHMENTS][$name]);
      else
        throw new \Exception("Can't find `$name` attachment in the document.");
    else
      throw new \Exception("The document doesn't have any attachment.");
  }


  //! @cond HIDDEN_SYMBOLS

  /**
   * @brief Returns the `_local_seq` number.
   */
  public function getLocalSequence() {
    return $this->meta[self::LOCAL_SEQUENCE];
  }


  /**
   * @brief Returns `true` is the `_local_sequence` number is present, `false` otherwise.
   */
  public function issetLocalSequence() {
    return $this->isMetadataPresent(self::LOCAL_SEQUENCE);
  }

  //! @endcond

}