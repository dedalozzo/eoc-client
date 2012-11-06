<?php

//! @file Attachment.php
//! @brief This file contains the Attachment class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


use Rest\Response;


//! @brief TODO
final class Attachment {
  use Properties;

  // Default CouchDB attachment content type. Here just for documentation.
  //const DEFAULT_ATTACHMENT_CONTENT_TYPE = "application/octet-stream";

  private $name;
  private $contentLength;
  private $contentType;
  private $data;


  private function __construct() {
  }


  public static function fromFile($fileName) {
    $instance = new self();

    if (file_exists($fileName)) {
      if (is_dir($fileName))
        throw new \Exception("The file $fileName is a directory.");

      $instance->name = basename($fileName);

      $fd = @fopen($fileName, "r");
      if (is_resource($fd)) {
        $instance->contentLength = filesize($fileName);

        $buffer = fread($fd, $instance->contentLength);
        $instance->data = base64_encode($buffer);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $instance->contentType = finfo_file($finfo, $fileName);

        finfo_close($finfo);
        fclose($fd);
      }
      else
        throw new \Exception("Cannot open the file $fileName.");
    }
    else
      throw new \Exception("The file $fileName doesn't exist.");

    return $instance;
  }


  public function asArray() {
    return [
      "content_lenght" => $this->contentType,
      "content_type" => $this->contentType,
      "data" => $this->data
    ];
  }


  public function getName() {
    return $this->name;
  }


  public function setName($value) {
    $this->name = (string)$value;
  }


  public function getContentType() {
    return $this->contentType;
  }


  public function getContentLength() {
    return $this->contentLength;
  }


  public function getData() {
    return $this->data;
  }

}

?>