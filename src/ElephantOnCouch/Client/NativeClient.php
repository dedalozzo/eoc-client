<?php

/**
 * @file NativeClient.php
 * @brief This file contains the NativeClient class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Client;


use ElephantOnCouch\Message\Message;
use ElephantOnCouch\Message\Request;
use ElephantOnCouch\Message\Response;
use ElephantOnCouch\Hook;


/**
 * @brief An HTTP 1.1 client using raw socket.
 * @details This client is using HTTP/1.1 version.\n
 * Encoding is made according RFC 3986, using rawurlencode().\n
 * It supports 100-continue, chunked responses, persistent connections, etc.
 */
class NativeClient extends AbstractClient {

  //! HTTP protocol version.
  const HTTP_VERSION = "HTTP/1.1";

  //! CR+LF (0x0D 0x0A). A Carriage Return followed by a Line Feed. We don't use PHP_EOL because HTTP wants CR+LF.
  const CRLF = "\r\n";

  //! Buffer dimension.
  const BUFFER_LENGTH = 8192;

  //! Maximum period to wait before the response is sent.
  const DEFAULT_TIMEOUT = 60000;

  private static $defaultSocketTimeout;

  // Socket handle.
  private $handle;

  // Socket connection timeout in seconds, specified by a float.
  private $timeout;


  public function __construct($server = parent::DEFAULT_SERVER, $userName = "", $password = "", $persistent = TRUE) {
    $this->initialize();

    parent::__construct($server, $userName, $password);

    $this->timeout = static::$defaultSocketTimeout;

    // Establishes a connection within the server.
    if ($persistent)
      $this->handle = @pfsockopen($this->scheme.$this->host, $this->port, $errno, $errstr, $this->timeout);
    else
      $this->handle = @fsockopen($this->scheme.$this->host, $this->port, $errno, $errstr, $this->timeout);

    if (!is_resource($this->handle))
      throw new \ErrorException($errstr, $errno);
  }


  public function initialize() {

    if (!static::$initialized) {
      static::$initialized = TRUE;

      // If PHP is not properly recognizing the line endings when reading files either on or created by a Macintosh
      // computer, enabling the auto_detect_line_endings run-time configuration option may help resolve the problem.
      ini_set("auto_detect_line_endings", TRUE);

      // By default the default_socket_timeout php.ini setting is used.
      static::$defaultSocketTimeout = ini_get("default_socket_timeout");
    }
  }


  // This method executes the provided request, using sockets.
  public function send(Request $request, Hook\IChunkHook $chunkHook = NULL) {
    $command = $request->getMethod()." ".$request->getPath().$request->getQueryString()." ".self::HTTP_VERSION;

    $request->setHeaderField(Request::HOST_HF, $this->host.":".$this->port);

    if (!empty($this->userName))
      $request->setBasicAuth($this->userName, $this->password);

    // Sets the Content-Length header only when the given request has a message body.
    if ($request->hasBody())
      $request->setHeaderField(Message::CONTENT_LENGTH_HF, $request->getBodyLength());

    // Writes the request over the socket.
    fputs($this->handle, $command.self::CRLF);
    fputs($this->handle, $request->getHeaderAsString().self::CRLF);
    fputs($this->handle, self::CRLF);
    fputs($this->handle, $request->getBody());
    fputs($this->handle, self::CRLF);

    // Reads the header.
    $header = "";

    while (!feof($this->handle)) {
      // We use fgets() because it stops reading at first newline or buffer length, depends which one is reached first.
      $buffer = fgets($this->handle, self::BUFFER_LENGTH);

      // Adds the buffer to the header.
      $header .= $buffer;

      // The header is separated from the body by a newline, so we break when we read it.
      if ($buffer == self::CRLF)
        break;
    }

    // Creates the Response object, that parses the header.
    $response = new Response($header);

    // Now it's time to read the response body.
    $body = "";

    // This might be a chunked response. See: http://www.jmarshall.com/easy/http/#http1.1c2.
    if ($response->getHeaderFieldValue(Response::TRANSFER_ENCODING_HF) == "chunked") {

      while (!feof($this->handle)) {
        // Gets the line which has the length of this chunk.
        $line = fgets($this->handle, self::BUFFER_LENGTH);

        // If it's only a newline, this normally means it's read the total amount of data requested minus the newline
        // continue to next loop to make sure we're done.
        if ($line == self::CRLF)
          continue;

        // The length of the block is expressed in hexadecimal.
        $length = hexdec($line);

        if (!is_int($length))
          throw new \RuntimeException("The response doesn't seem chunk encoded.");

        // Zero is sent when at the end of the chunks or the end of the stream.
        if ($length < 1)
          break;

        // Reads the chunk.
        // When reading from network streams or pipes, such as those returned when reading remote files or from popen()
        // and proc_open(), reading will stop after a new packet is available. This means that we must collect the data
        // together in chunks. So, we can't pass to the fread() the entire length because it could return less data than
        // expected. We have to read, instead, the standard buffer length, and concatenate the read chunks.
        $buffer = "";

        while ($length > 0) {
          $size = min(self::BUFFER_LENGTH, $length);
          $data = fread($this->handle, $size);

          if (strlen($data) == 0)
            break; // EOF

          $buffer .= $data;
          $length -= strlen($data);
        }

        // If a function has been hooked, calls it, else just adds the buffer to the body.
        if (is_null($chunkHook))
          $body .= $buffer;
        else
          $chunkHook->process($buffer);
      }

      // A chunk response might have some footer, but CouchDB doesn't use them, so we simply ignore them.
      while (!feof($this->handle)) {
        // We use fgets() because it stops reading at first newline or buffer length, depends which one is reached first.
        $buffer = fgets($this->handle, self::BUFFER_LENGTH);

        // The chunk response ends with a newline, so we break when we read it.
        if ($buffer == self::CRLF)
          break;
      }

    }
    else { // Normal response, not chunked.
      // Retrieves the body length from the header.
      $length = (int)$response->getHeaderFieldValue(Response::CONTENT_LENGTH_HF);

      // The response should have a body, if not we have finished.
      if ($length > 0) {
        $bytes = 0;

        while (!feof($this->handle)) {
          $buffer = fgets($this->handle);
          $body .= $buffer;
          $bytes += strlen($buffer);

          if ($bytes >= $length)
            break;
        }
      }
    }

    // Assigns the body to the Response, if any is present.
    $response->setBody($body);

    return $response;
  }

} 