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

  public $file_name = "";

  public $mime_type = self::DEFAULT_ATTACHMENT_CONTENT_TYPE;
  public $file_size = 0;

  public $rev = "";
  public $data = NULL;
}

?>