<?php

//! @file DbUpdatesFeedOpts.php
//! @brief This file contains the DbUpdatesFeedOpts class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Opt;


//! @brief To retrieve additional information about document, you can create a ChangesFeedOpts instance and pass it as parameter
//! to the Couch.getDoc() method.
//! @nosubgrouping
//! @see http://docs.couchdb.org/en/latest/changes.html#changes
class DbUpdatesFeedOpts extends AbstractOpts {

  //! Period in seconds to wait for a change before the response is sent, even if there are no results.
  const DEFAULT_TIMEOUT = 60;

  //! @name Feed Types
  //@{

  //! @brief Long polling mode.
  //! @details The longpoll feed (probably most useful used from a browser) is a more efficient form of polling that waits
  //! for a change to occur before the response is sent. longpoll avoids the need to frequently poll CouchDB to discover
  //! nothing has changed.
  const LONGPOLL_TYPE = "longpoll";

  //! @brief Continuous (non-polling) mode.
  //! @details Polling the CouchDB server is not a good thing to do. Setting up new HTTP connections just to tell the
  //! client that nothing happened puts unnecessary strain on CouchDB.
  //! A continuous feed stays open and connected to the database until explicitly closed and changes are sent to the
  //! client as they happen, i.e. in near real-time.
  const CONTINUOUS_TYPE = "continuous";

  //! @brief The eventsource feed provides push notifications that can be consumed in the form of DOM events in the browser.
  //! @see http://www.w3.org/TR/eventsource/
  const EVENTSOURCE_TYPE = "eventsource";

  //@}


  private static $supportedTypes = [
    self::LONGPOLL_TYPE => NULL,
    self::CONTINUOUS_TYPE => NULL,
    self::EVENTSOURCE_TYPE => NULL
  ];


  //! @brief Sets the type of feed.
  //! @param[in] string $type Type of feed.
  public function setFeedType($type) {
    if (array_key_exists($type, self::$supportedTypes))
      $this->options["feed"] = $type;
    else
      throw new \InvalidArgumentException("Invalid feed type.");
  }


  //! @brief Maximum period in seconds to wait for a change before the response is sent, even if there are no results.
  //! @details Note that 60 is also the default maximum timeout to prevent undetected dead connections.
  //! @param[in] integer $timeout Maximum period to wait before the response is sent. Must be a positive integer.
  //! @warning Only applicable for <i>continuous</i> feeds.
  public function setTimeout($timeout = self::DEFAULT_TIMEOUT) {
    $feed = $this->options['feed'];

    if ($feed == self::CONTINUOUS_TYPE)
      if (is_int($timeout) and ($timeout > 0))
        $this->options["timeout"] = $timeout;
      else
        throw new \InvalidArgumentException("\$timeout must be a positive integer.");
  }

}