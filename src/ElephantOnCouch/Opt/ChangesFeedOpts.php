<?php

//! @file ChangesFeedOpts.php
//! @brief This file contains the ChangesFeedOpts class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Opt;


//! @brief To retrieve additional information about document, you can create a ChangesFeedOpts instance and pass it as parameter
//! to the ElephantOnCouch <i>getDoc</i> method.
//! @nosubgrouping
class ChangesFeedOpts {
  use \ElephantOnCouch\Properties;

  //! @name Properties
  //@{

  //@}

  private $options = [];


  //! @name Feeds
  //@{
  const NORMAL_FEED = "normal"; //!< Normal mode.
  const CONTINUOUS_FEED = "continuous"; //!< Continuous (non-polling) mode.
  const LONGPOLL_FEED = "longpoll"; //!< Long polling mode.
  //@}

  //! @name Styles (used in the getDbChanges method)
  //@{
  const MAIN_ONLY_STYLE = "main_only";
  const ALL_DOCS_STYLE = "all_docs";
  //@}



  //! @brief Resets the options.
  public function reset() {
    unset($this->options);
    $this->options = [];
  }


  //! @brief Returns an associative array of the chosen options. Used internally by ElephantOnCouch <i>getDoc</i> method.
  //! @return associative array
  public function asArray() {
    return $this->options;
  }


  //! @brief Automatically fetches and includes full documents.
  //! @warning You can call this method only if the view doesn't contain a reduce function.
  public function includeDocs() {
    $this->options["include_docs"] = "true";
  }


  //! @brief Reverses order of results.
  public function reverseOrderOfResults() {
    $this->options["descending"] = "true";
  }


  //! @param[in] integer $heartbeat Period, in milliseconds, after which an empty line is sent during longpoll or
  //! continuous. Must be a positive integer.
  //! @param[in] integer $since Start the results from the specified sequence number.
  //! @param[in] integer $limit Maximum number of rows to return. Must be a positive integer.
  //! @param[in] string $feed Type of feed.
  //! @param[in] integer $heartbeat Period in milliseconds after which an empty line is sent in the results. Only
  //! applicable for <i>longpoll</i> or <i>continuous</i> feeds. Overrides any timeout to keep the feed alive indefinitely.
  //! @param[in] integer $timeout Maximum period to wait before the response is sent. Must be a positive integer.
  //! @param[in] bool $style Specifies how many revisions are returned in the changes array. The default, <i>main_only</i>,
  //! will only return the winning revision; <i>all_docs</i> will return all the conflicting revisions.
  //! @param[in] string $filter Filter function from a design document to get updates.



since	seqnum / now	0	(1)
limit	maxsequences	none	(2)
descending	boolean	false	(3)
feed	normal / longpoll / continuous / eventsource	normal	(4)
heartbeat	milliseconds	60000	(5)
timeout	milliseconds	60000	(6)
filter	designdoc/filtername / _view	none	(7)
style	all_docs / main_only	main_only	(9)
view	designdoc/filtername	none	(10)


}