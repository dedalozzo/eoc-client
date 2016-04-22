<?php

/**
 * @file ChangesFeedOpts.php
 * @brief This file contains the ChangesFeedOpts class.
 * @details
 * @author Filippo F. Fadda
 */


namespace EoC\Opt;


/**
 * @brief To change the feed type, set a different timeout, etc, you can create a ChangesFeedOpts instance and pass
 * it as parameter to the Couch::getDbChanges() method.
 * @nosubgrouping
 * @see http://docs.couchdb.org/en/latest/changes.html#changes
 */
class ChangesFeedOpts extends AbstractOpts {

  //! Default period after which an empty line is sent during a longpoll or continuous feed.
  const DEFAULT_HEARTBEAT = 60000;

  //! Period in milliseconds to wait for a change before the response is sent, even if there are no results.
  const DEFAULT_TIMEOUT = 60000;

  /** @name Feed Types */
  //!@{

  //! Normal mode.
  const NORMAL_TYPE = "normal";

  /**  
   * @brief Long polling mode.
   * @details The longpoll feed (probably most useful used from a browser) is a more efficient form of polling that waits
   * for a change to occur before the response is sent. longpoll avoids the need to frequently poll CouchDB to discover
   * nothing has changed.
   */
  const LONGPOLL_TYPE = "longpoll";

  /**
   * @brief Continuous (non-polling) mode.
   * @details Polling the CouchDB server is not a good thing to do. Setting up new HTTP connections just to tell the
   * client that nothing happened puts unnecessary strain on CouchDB.
   * A continuous feed stays open and connected to the database until explicitly closed and changes are sent to the
   * client as they happen, i.e. in near real-time.
   */
  const CONTINUOUS_TYPE = "continuous";

  /*
   * @brief The eventsource feed provides push notifications that can be consumed in the form of DOM events in the browser.
   * @see http://www.w3.org/TR/eventsource/
   */
  const EVENTSOURCE_TYPE = "eventsource";

  //!@}

  /** @name Feed Styles */
  //!@{
  const MAIN_ONLY_STYLE = "main_only";
  const ALL_DOCS_STYLE = "all_docs";
  //!@}


  private static $supportedStyles = array( // Cannot use [] syntax otherwise Doxygen generates a warning.
    self::MAIN_ONLY_STYLE => NULL,
    self::ALL_DOCS_STYLE => NULL
  );

  private static $supportedTypes = array( // Cannot use [] syntax otherwise Doxygen generates a warning.
    self::NORMAL_TYPE => NULL,
    self::LONGPOLL_TYPE => NULL,
    self::CONTINUOUS_TYPE => NULL,
    self::EVENTSOURCE_TYPE => NULL
  );


  /**
   * @brief Starts the results from the change immediately after the given sequence number.
   * @param[in] integer $since Sequence number to start results. Allowed values: positive integers | 'now'.
   */
  public function setSince($since = 0) {
    if (($since == "now") or (is_int($since) and ($since >= 0)))
      $this->options["since"] = $since;
    else
      throw new \InvalidArgumentException("\$since must be a non-negative integer or can be 'now'.");

    return $this;
  }


  /**
   * @brief Limits number of result rows to the specified value.
   * @param[in] integer $limit Maximum number of rows to return. Must be a positive integer.
   */
  public function setLimit($limit) {
    if (is_int($limit) and ($limit > 0))
      $this->options["limit"] = $limit;
    else
      throw new \InvalidArgumentException("\$value must be a positive integer.");

    return $this;
  }


  /**
   * @brief Reverses order of results to return the change results in descending sequence order (most recent change first).
   */
  public function reverseOrderOfResults() {
    $this->options["descending"] = "true";
  }


  /**
   * @brief Sets the type of feed.
   * @param[in] string $type Type of feed.
   */
  public function setFeedType($type) {
    if (array_key_exists($type, self::$supportedTypes))
      $this->options["feed"] = $type;
    else
      throw new \InvalidArgumentException("Invalid feed type.");
  }


  /**
   * @brief Specifies how many revisions are returned in the changes array. The default, `main_only`, will only
   * return the winning revision; `all_docs` will return all the conflicting revisions.
   * @param[in] bool $style The feed style.
   */
  public function setStyle($style) {
    if (array_key_exists($style, self::$supportedStyles))
      $this->options["style"] = $style;
    else
      throw new \InvalidArgumentException("Invalid feed style.");
  }


  /**
   * @brief Period in milliseconds after which an empty line is sent in the results. Overrides any timeout to keep the
   * feed alive indefinitely.
   * @param[in] integer $heartbeat Period in milliseconds after which an empty line is sent in the results.
   * @warning Only applicable for `longpoll` or `continuous` feeds.
   */
  public function setHeartbeat($heartbeat = self::DEFAULT_HEARTBEAT) {
    $feed = $this->options['feed'];

    if (($feed == self::CONTINUOUS_TYPE) or ($feed == self::LONGPOLL_TYPE))
      if (is_int($heartbeat) and ($heartbeat >= 0))
        $this->options["heartbeat"] = $heartbeat;
      else
        throw new \InvalidArgumentException("\$heartbeat must be a non-negative integer.");
  }


  /**
   * @brief Maximum period in milliseconds to wait for a change before the response is sent, even if there are no results.
   * @details Note that 60000 is also the default maximum timeout to prevent undetected dead connections.
   * @param[in] integer $timeout Maximum period to wait before the response is sent. Must be a positive integer.
   * @warning Only applicable for `longpoll` or `continuous` feeds.
   */
  public function setTimeout($timeout = self::DEFAULT_TIMEOUT) {
    $feed = $this->options['feed'];

    if (($feed == self::CONTINUOUS_TYPE) or ($feed == self::LONGPOLL_TYPE))
      if (is_int($timeout) and ($timeout > 0))
        $this->options["timeout"] = $timeout;
      else
        throw new \InvalidArgumentException("\$timeout must be a positive integer.");
  }


  /**
   * @brief Automatically fetches and includes full documents.
   */
  public function includeDocs() {
    $this->options["include_docs"] = "true";
  }


  /**
   * @brief Sets a filter function.
   * @param[in] string $designDocName The design document's name.
   * @param[in] string $filterName Filter function from a design document to get updates.
   * @todo Implement the setFilter() method.
   */
  public function setFilter($designDocName, $filterName) {
    //if (is_string($designDocName) && !empty($designDocName)) &&
  }

}