<?php

//! @file ChangesFeedOpts.php
//! @brief This file contains the ChangesFeedOpts class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Opt;


use ElephantOnCouch\Opt\AbstractOpts;
use ElephantOnCouch\Enum\FeedType;
use ElephantOnCouch\Enum\FeedStyle;


//! @brief To retrieve additional information about document, you can create a ChangesFeedOpts instance and pass it as parameter
//! to the ElephantOnCouch <i>getDoc</i> method.
//! @nosubgrouping
//! @see http://docs.couchdb.org/en/latest/changes.html#changes
class ChangesFeedOpts extends AbstractOpts {

  //! Default period after which an empty line is sent during a longpoll or continuous feed.
  const DEFAULT_HEARTBEAT = 60000;

  //! period in milliseconds to wait for a change before the response is sent, even if there are no results.
  const DEFAULT_TIMEOUT = 60000;


  //! @brief Starts the results from the change immediately after the given sequence number.
  //! @param[in] integer $since Sequence number to start results. Allowed values: positive integers | 'now'.
  //! @exception Exception <c>Message: <i>\$since must be a non-negative integer or can be 'now'.</i></c>
  public function setSince($since = 0) {
    if (($since == "now") or (is_int($since) and ($since >= 0)))
      $this->options["since"] = $since;
    else
      throw new \Exception("\$since must be a non-negative integer or can be 'now'.");
  }


  //! @brief Limits number of result rows to the specified value.
  //! @exception Exception <c>Message: <i>\$limit must be a positive integer.</i></c>
  //! @param[in] integer $limit Maximum number of rows to return. Must be a positive integer.
  public function setLimit($limit) {
    if (is_int($limit) and ($limit > 0))
      $this->options["limit"] = $limit;
    else
      throw new \Exception("\$value must be a positive integer.");
  }


  //! @brief Reverses order of results to return the change results in descending sequence order (most recent change first).
  public function reverseOrderOfResults() {
    $this->options["descending"] = "true";
  }


  //! @brief Sets the type of feed.
  //! @param[in] string $type Type of feed.
  //! @exception Exception <c>Message: <i>\$feed is not supported.</i></c>
  public function setFeedType(FeedType $type) {
    $this->options["feed"] = $type;
  }


  //! @brief Specifies how many revisions are returned in the changes array. The default, <i>main_only</i>, will only
  //! return the winning revision; <i>all_docs</i> will return all the conflicting revisions.
  //! @param[in] bool $style The feed style.
  public function setStyle(FeedStyle $style) {
    $this->options["style"] = $style;
  }


  //! @brief Period in milliseconds after which an empty line is sent in the results. Overrides any timeout to keep the
  //! feed alive indefinitely.
  //! @param[in] integer $heartbeat Period in milliseconds after which an empty line is sent in the results.
  //! @exception Exception <c>Message: <i>\$heartbeat must be a non-negative integer.</i></c>
  //! @warning Only applicable for <i>longpoll</i> or <i>continuous</i> feeds.
  public function setHeartbeat($heartbeat = self::DEFAULT_HEARTBEAT) {
    $feed = $this->options['feed'];

    if (($feed == FeedType::CONTINUOUS) or ($feed == FeedType::LONGPOLL))
      if (is_int($heartbeat) and ($heartbeat >= 0))
        $this->options["heartbeat"] = $heartbeat;
      else
        throw new \Exception("\$heartbeat must be a non-negative integer.");
  }


  //! @brief Maximum period in milliseconds to wait for a change before the response is sent, even if there are no results.
  //! @details Note that 60000 is also the default maximum timeout to prevent undetected dead connections.
  //! @param[in] integer $timeout Maximum period to wait before the response is sent. Must be a positive integer.
  //! @exception Exception <c>Message: <i>\$timeout must be a positive integer.</i></c>
  //! @warning Only applicable for <i>longpoll</i> or <i>continuous</i> feeds.
  public function setTimeout($timeout = self::DEFAULT_TIMEOUT) {
    $feed = $this->options['feed'];

    if (($feed == FeedType::CONTINUOUS) or ($feed == FeedType::LONGPOLL))
      if (is_int($timeout) and ($timeout > 0))
        $this->options["timeout"] = $timeout;
      else
        throw new \Exception("\$timeout must be a positive integer.");
  }


  //! @brief Automatically fetches and includes full documents.
  public function includeDocs() {
    $this->options["include_docs"] = "true";
  }


  //! @brief
  //! @param[in] string $filter Filter function from a design document to get updates.  designdoc/filtername
  public function setFilter(\SplString $designDocName, $filterName) {
    //if (is_string($designDocName) && !empty($designDocName)) &&
  }




//$request->setQueryParam("filter", $filter);


//$style = self::MAIN_ONLY_STYLE,
//$filter = ""


  //! @exception Exception <c>Message: <i>No database selected.</i></c>



  //! view	designdoc/filtername	none	(10)



}