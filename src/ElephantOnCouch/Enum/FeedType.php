<?php

//! @file FeedType.php
//! @brief This file contains the FeedType class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Enum;


//! @brief Feed Types Enumerator.
//! @nosubgrouping
class FeedType extends \SplEnum {

  const __default = self::NORMAL;

  //! @name Feed Types
  //@{

  //! @brief Normal mode.
  const NORMAL = "normal";

  //! @brief Long polling mode.
  //! @details The longpoll feed (probably most useful used from a browser) is a more efficient form of polling that waits
  //! for a change to occur before the response is sent. longpoll avoids the need to frequently poll CouchDB to discover
  //! nothing has changed.
  const LONGPOLL = "longpoll";

  //! @brief Continuous (non-polling) mode.
  //! @details Polling the CouchDB server is not a good thing to do. Setting up new HTTP connections just to tell the
  //! client that nothing happened puts unnecessary strain on CouchDB.
  //! A continuous feed stays open and connected to the database until explicitly closed and changes are sent to the
  //! client as they happen, i.e. in near real-time.
  const CONTINUOUS = "continuous";

  //! @brief The eventsource feed provides push notifications that can be consumed in the form of DOM events in the browser.
  //! @see http://www.w3.org/TR/eventsource/
  const EVENTSOURCE = "eventsource";

  //@}

}