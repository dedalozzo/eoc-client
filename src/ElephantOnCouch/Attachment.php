<?php

//! @file Attachment.php
//! @brief This file contains the Attachment class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief TODO
class Attachment {
  //! Default CouchDB attachment content type.
  const DEFAULT_ATTACHMENT_CONTENT_TYPE = "application/octet-stream";

  private $name = "";

  private $mimeType = self::DEFAULT_ATTACHMENT_CONTENT_TYPE;
  private $fileSize = 0;

  private $rev = "";
  private $data = NULL;


  public static function uploadedByPost($fileName) {
    $instance = new self();

    $this->fileName =
    $this->data =


    return $instance;
  }


  public static function readFromMemory() {
    $instance = new self();

    return $instance;
  }


  public static function loadFromDisk($fileName) {
    $instance = new self();

    return $instance;
  }


  public function asArray() {
    $attachment[$this->name] = $this->contentType;
    $attachment[$this->name] = $this->contentType;
    $attachment[$data] = $this->data;

    $view[self::MAP] = $this->mapFn;

    if (!empty($this->reduceFn))
      $view[self::REDUCE] = $this->reduceFn;

    if (!empty($this->options))
      $view[self::OPTIONS] = $this->options;

    return $attachment;
  }

}

?>