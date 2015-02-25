<?php

/**
 * @file AttachmentInfo.php
 * @brief This file contains the AttachmentInfo class.
 * @details
 * @author Filippo F. Fadda
 */


namespace EoC\Info;


use EoC\Message\Response;


/**
 * @brief This is an information only purpose class. It's used by Couch::getAttachmentInfo() method.
 * @nosubgrouping
 */
class AttachmentInfo {


  /**
   * @brief Creates the object.
   * @param[in] Response $response A response object.
   */
  public function __construct(Response $response) {
    $this->response = $response;
  }


  /**
   * @brief Tells if the attachment is range request aware.
   * @details Used for attachments with `application/octet-stream` content type.
   */
  public function doesAcceptRanges() {
    return $this->response->hasHeaderFile(Response::ACCEPT_RANGES_HF);
  }


  /**
   * @brief Codec used to compress the attachment.
   * @details Only available if attachment’s `content_type` is in list of compressible types.
   * @return string|bool The codec name or `false` if the attachment is not compressed.
   */
  public function getContentEncoding() {
    if ($this->response->hasHeaderField(Response::CONTENT_ENCODING_HF))
      return $this->response->getHeaderFieldValue(Response::CONTENT_ENCODING_HF);
    else
      return FALSE;
  }


  /**
   * @brief Attachment size.
   * @details If a codec was used to compress the file, the method returns the compressed size.
   * @return integer
   */
  public function getSize() {
    return $this->response->getHeaderFieldValue(Response::CONTENT_LENGTH_HF);
  }


  /**
   * @brief Base64 encoded MD5 binary digest.
   * @return integer
   */
  public function getMD5() {
    return $this->response->getHeaderFieldValue(Response::CONTENT_MD5_HF);
  }


  /**
   * @brief Double quoted base64 encoded MD5 binary digest
   * @return integer
   */
  public function getDoubleQuotedMD5() {
    return $this->response->getHeaderFieldValue(Response::ETAG_HF);
  }

}