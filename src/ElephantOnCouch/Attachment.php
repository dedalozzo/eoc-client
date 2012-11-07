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
  private $stub;
  private $contentLength;
  private $contentType;
  private $data;


  private function __construct() {
    $this->stub = FALSE;
  }


  public static function fromFile($fileName) {
    $instance = new self();

    if (file_exists($fileName)) {
      if (is_dir($fileName))
        throw new \Exception("The file $fileName is a directory.");

      $instance->name = basename($fileName);

      $fd = @fopen($fileName, "rb");
      if (is_resource($fd)) {
        $instance->contentLength = filesize($fileName);

        $instance->data = fread($fd, $instance->contentLength);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $instance->contentType = finfo_file($finfo, $fileName);

        finfo_close($finfo);
        fclose($fd);

        if ($instance->data === FALSE)
          throw new \Exception("Error reading the file $fileName.");
      }
      else
        throw new \Exception("Cannot open the file $fileName.");
    }
    else
      throw new \Exception("The file $fileName doesn't exist.");

    return $instance;
  }


  public static function fromArray(array $array) {
    $instance = new self();

    $instance->name = key($array);
    $meta = reset($array);
    $instance->stub = (array_key_exists("stub", $meta)) ? TRUE : FALSE;
    $instance->contentLength = $meta["lenght"];
    $instance->contentType = $meta["content_type"];
    $instance->data = base64_decode($meta["data"]);

    return $instance;
  }


  public function asArray() {
    return [
      "content_type" => $this->contentType,
      "data" => base64_encode($this->data)
    ];
  }


  public function save($overwrite = TRUE) {
    $mode = ($overwrite) ? "wb" : "xb";

    $fd = @fopen($fileName, $mode);
    if (is_resource($fd)) {
      $bytes = fwrite($fd, $this->data);
      fclose($fd);

      if ($bytes === FALSE)
        throw new \Exception("Error writing the file $fileName.");
    }
    else
      throw new \Exception("Cannot create the file $fileName.");
  }



  public function getName() {
    return $this->name;
  }


  public function setName($value) {
    $this->name = (string)$value;
  }


  public function getStub() {
    return $this->stub;
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