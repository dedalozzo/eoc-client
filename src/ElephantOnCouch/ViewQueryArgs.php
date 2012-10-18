<?php

//! @file ViewQueryArgs.php
//! @brief This file contains the ViewQueryArgs class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief TODO
//! @nosubgrouping
//! @todo Add 'list' and 'callback' properties.
class ViewQueryArgs {

  private $options;

  //! @brief Search for documents with the same emitted key.
  private $key = "";

  //! @brief Used to retrieve just the view rows matching that set of keys. Rows are returned in the order of the
  //! specified keys. Combining this feature with include_docs=true results in the so-called multi-document-fetch feature.
  private $keys = [];

  //! @name Key Range
  //! @brief Those are used to return documents in a key range.
  //@{
  private $startKey = "";
  private $endKey = "";
  //@}

  //! @name First and Last Documents Identifiers
  //! @brief First and last documents to be included in the output.
  //! @details If you expect to have multiple documents emit identical keys, you'll need to use <b>$startDocId</b> in
  //! addition to <b>$startKey</b> to paginate correctly. The reason is that <b>$startKey</b> alone will no longer be
  //! sufficient to uniquely identify a row.
  //@{
  private $startDocId = "";
  private $endDocId = "";
  //@}


  //! @brief Reset default options.
  public function reset() {
    unset($this->options);
    $this->options = [];
  }


  public function asArray() {
    return $this->options;
  }


  public function setStartDocId($startDocId) {
    $this->startDocId = $startDocId;
  }


  public function setStartKey($startKey) {
    $this->startKey = $startKey;
  }


  public function setEndDocId($endDocId) {
    $this->endDocId = $endDocId;
  }


  public function setEndKey($endKey) {
    $this->endKey = $endKey;
  }


  public function setKey($key) {
    $this->key = $key;
  }


  //! @brief Restricts the number of results.
  public function setLimit($value) {
    if (is_int($value) && $value > 0)
      $this->options["limit"] = $value;
    else
      throw new \Exception("\$value must be a positive integer.");
  }


  //! @brief Results should be grouped.
  //! @details The group option controls whether the reduce function reduces to a set of distinct keys or to a single
  //! result row. This will run the rereduce procedure.
  public function groupResults() {
    $this->options["group"] = "true";
  }


  //! @brief Level at which documents should be grouped.
  //! @details Allowed values: positive integers.
  public function setGroupLevel($value) {
    if (is_int($value) && $value > 0) {
      $this->options["group"] = "true"; // This parameter is used only if 'group' is 'true'.
      $this->options["limit"] = $value;
    }
    else
      throw new \Exception("\$value must be a positive integer.");
  }


  //! @brief Even is a reduce function is defined for the view, doesn't call it.
  //! @details If a view contains both a map and reduce function, querying that view will by default return the result
  //! of the reduce function. To avoid this behaviour you must call this method.
  public function doNotReduce() {
    $this->options["reduce"] = "false";
  }


  //! @brief Automatically fetches and includes full documents.
  //! @details However, the user should keep in mind that there is a race condition when using this option. It is
  //! possible that between reading the view data and fetching the corresponding document that the document has changed.
  public function includeDocs() {
    $this->options["include_docs"] = "true";
  }


  //! @brief Don't get any data, but all meta-data for this View. The number of documents in this View for example.
  public function excludeResults() {
    $this->options["limit"] = 0;
  }


  //! @brief Tells CouchDB to not include end key in the result.
  public function excludeEndKey() {
    $this->options["inclusive_end"] = "false";
  }


  //! @name View Refresh Controls
  //! @brief Don't refresh views for quicker results.
  //! @details The stale option can be used for higher performance at the cost of possibly not seeing the all latest
  //! documents.
  //! CouchDB defaults to regenerating views the first time they are accessed. This behavior is preferable in most cases
  //! as it optimizes the resource utilization on the database server. On the other hand, in some situations the benefit
  //! of always having fast and updated views far outweigh the cost of regenerating them every time the database server
  //! receives updates. You can chose CouchDB behaviour using one of the following methods.
  //! Those methods essentially tell CouchDB that if a reference to the view index is available in memory (ie, if
  //! the view has been queried at least once since couch was started), go ahead and use it, even if it may be out of
  //! date. The result is that for a highly trafficked view, end users can see lower latency, although they may not get
  //! the latest data. However, if there is no view index pointer in memory, the behavior with this option is that same
  //! as the behavior without the option.
  //@{

  //! @brief CouchDB will not refresh the view, even if it's stalled.
  //! @details This is useful in case you chose to not refresh a view when a query is performed on it, because you want
  //! faster results. Remember, in case, to use an updater script that calls the views periodically, for example using a
  //! cron. You can find the implementation in Ruby or Python in the document
  //! \"<a href="http://wiki.apache.org/couchdb/Regenerating_views_on_update" target="_blank">Update views on document save</a>\".
  public function doNotRefreshView() {
    $this->options["stale"] = "ok";
  }


  //! @brief CouchDB will update the view after the query's result is returned.
  public function queryThenRefreshView() {
    $this->options["stale"] = "update_after";
  }

  //@}


  //! @brief Reverses order of results.
  //! @details Note that the descending option is applied before any key filtering, so you may need to swap the values
  //! of the start key and end key options to get the expected results.
  public function reverseOrderOfResults() {
    $this->options["descending"] = "false";
  }


  //! @brief Skip the defined number of documents.
  //! @details The skip option should only be used with small values, as skipping a large range of documents this way is
  //! inefficient (it scans the index from the start key and then skips N elements, but still needs to read all the index
  //! values to do that). For efficient paging you'll need to use start key and limit.
  //! Allowed values: positive integers.
  public function skipDocs($number) {
    if (is_int($number) && $number > 0)
      $this->options["skip"] = $number;
    else
      throw new \Exception("\$value must be a positive integer.");
  }


  //! @brief Includes conflict documents.
  public function includeConflicts() {
    $this->options["conflicts"] = "true";
  }

}

?>
