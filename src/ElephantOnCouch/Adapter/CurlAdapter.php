<?php

/**
 * @file CurlAdapter.php
 * @brief This file contains the CurlAdapter class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Adapter;


use ElephantOnCouch\Message\Request;
use ElephantOnCouch\Message\Response;
use ElephantOnCouch\Hook;


/**
 * @brief An HTTP 1.1 client adapter using cURL.
 * @nosubgrouping
 * @attention To use this client adapter, cURL must be installed on server.
 */
class CurlAdapter extends AbstractAdapter {

  // cURL handle.
  private $handle;


  /**
   * @copydoc AbstractAdapter::__construct()
   */
  public function __construct($server = parent::DEFAULT_SERVER, $userName = "", $password = "") {
    $this->initialize();

    parent::__construct($server, $userName, $password);

    // Init cURL.
    $this->handle = curl_init();
  }


  /**
   * @brief Destroys the cURL handle.
   */
  public function __destruct() {
    curl_close($this->handle);
  }


  /**
   * @copydoc AbstractAdapter::initialize()
   */
  public function initialize() {
    if (!extension_loaded("curl"))
      throw new \RuntimeException("The cURL extension is not loaded.");
  }


  /**
   * @copydoc AbstractAdapter::send()
   */
  public function send(Request $request, Hook\IChunkHook $chunkHook = NULL) {
    $opts = [];

    // Resets all the cURL options. The curl_reset() function is available only since PHP 5.5.
    if (function_exists('curl_reset'))
      curl_reset($this->handle);

    // Sets the methods and its related options.
    switch ($request->getMethod()) {

      // GET method.
      case Request::GET_METHOD:
        $opts[CURLOPT_HTTPGET] = TRUE;
        break;

      // POST method.
      case Request::POST_METHOD:
        $opts[CURLOPT_POST] = TRUE;

        // The full data to post in a HTTP "POST" operation. To post a file, prepend a filename with @ and use the full
        // path. This can either be passed as a urlencoded string like 'para1=val1&para2=val2&...' or as an array with
        // the field name as key and field data as value. If value is an array, the Content-Type header will be set to
        // multipart/form-data.
        $opts[CURLOPT_POSTFIELDS] = $request->getBody();
        break;

      // PUT method.
      case Request::PUT_METHOD:
        $opts[CURLOPT_PUT] = TRUE;

        // Often a request contains data in the form of a JSON object. Since cURL is just able to read data from a file,
        // but we can't create a temporary file because it's a too much expensive operation, the code below uses a faster
        // and efficient memory stream.
        if ($request->hasBody()) {
          if ($fd = fopen("php://memory", "r+")) { // Try to create a temporary file in memory.
            fputs($fd, $request->getBody()); // Writes the message body.
            rewind($fd); // Sets the pointer to the beginning of the file stream.

            $opts[CURLOPT_INFILE] = $fd;
            $opts[CURLOPT_INFILESIZE] = $request->getBodyLength();
          }
          else
            throw new \RuntimeException("Cannot create the stream.");
        }

        break;

      // DELETE method.
      case Request::DELETE_METHOD:
        $opts[CURLOPT_CUSTOMREQUEST] = Request::DELETE_METHOD;
        break;

      // COPY or any other custom method.
      default:
        $opts[CURLOPT_CUSTOMREQUEST] = $request->getMethod();

    } // switch

    // Sets the request Uniform Resource Locator.
    $opts[CURLOPT_URL] = "http://".$this->host.":".$this->port.$request->getPath().$request->getQueryString();

    // Includes the header in the output. We need this because our Response object will parse them.
    // NOTE: we don't include header anymore, because we use the option CURLOPT_HEADERFUNCTION.
    //$opts[CURLOPT_HEADER] = TRUE;

    // Returns the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
    $opts[CURLOPT_RETURNTRANSFER] = TRUE;

    // Sets the protocol version to be used. cURL constants have different values.
    $opts[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;

    // Sets basic authentication.
    if (!empty($this->userName)) {
      $opts[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
      $opts[CURLOPT_USERPWD] = $this->userName.":".$this->password;
    }

    // Sets the previous options.
    curl_setopt_array($this->handle, $opts);

    // This fix a known cURL bug: see http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
    // cURL sets the Expect header field automatically, ignoring the fact that a client may not need it for the specific
    // request.
    if (!$request->hasHeaderField(Request::EXPECT_HF))
      curl_setopt($this->handle, CURLOPT_HTTPHEADER, array("Expect:"));

    // Sets the request header.
    // Due to a stupid bug, using curl_setopt_array(), cURL doesn't override the Content-Type header field. So we must
    // set the header using, instead, curl_stopt()
    // $opts[CURLOPT_HTTPHEADER] = $request->getHeaderAsArray();
    curl_setopt($this->handle, CURLOPT_HTTPHEADER, $request->getHeaderAsArray());

    // Here we use this option because we might have a response without body. This may happen because we are supporting
    // chunk responses, and sometimes we want trigger an hook function to let the user perform operations on coming
    // chunks.
    $header = "";
    curl_setopt($this->handle, CURLOPT_HEADERFUNCTION,
      function($unused, $buffer) use (&$header) {
        $header .= $buffer;
        return strlen($buffer);
      });

    // When the hook function is provided, we set the CURLOPT_WRITEFUNCTION so cURL will call the hook function for each
    // response chunk read.
    if (isset($chunkHook)) {
      curl_setopt($this->handle, CURLOPT_WRITEFUNCTION,
        function($unused, $buffer) use ($chunkHook) {
          $chunkHook->process($buffer);
          return strlen($buffer);
        });
    }

    if ($result = curl_exec($this->handle)) {
      $response = new Response($header);
      $response->setBody($result);
      return $response;
    }
    else {
      $error = curl_error($this->handle);
      throw new \RuntimeException($error);
    }
  }

} 