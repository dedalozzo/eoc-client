<?php

//! @file ViewQueryOpts.php
//! @brief This file contains the ViewQueryOpts class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Opt;


//! @brief To set the query arguments you must create an instance of this class. Use it when you query a CouchDB View with
//! the methods Couch.queryAllDocs(), Couch.queryView() and Couch.queryTempView().
//! @nosubgrouping
//! @todo Add 'list' property
//! @todo Add 'callback' property.
class ViewQueryOpts extends AbstractOpts {

  private $includeMissingKeys = FALSE;


  public function reset() {
    $this->includeMissingKeys = FALSE;
    parent::reset();
  }


  //! @brief Returns only documents that match the specified key.
  //! @param[in] string $value The key.
  //! @param[in] boolean $encode (optional) JSON encodes `$value`.
  public function setKey($value, $encode = TRUE) {
    $this->options["key"] = $encode ? json_encode($value) : $value;
    return $this;
  }


  //! @name Key Range
  //! @brief Those are used to return documents in a key range.
  //@{

  //! @brief Defines the first key to be included in the range.
  //! @param[in] string $value The key at which to start the range.
  //! @param[in] boolean $encode (optional) JSON encodes `$value`.
  public function setStartKey($value, $encode = TRUE) {
    $this->options["startkey"] = $encode ? json_encode($value) : $value;
    return $this;
  }


  //! @brief Defines the last key to be included in the range.
  //! @param[in] string $value The key at which to end the range.
  //! @param[in] boolean $encode (optional) JSON encodes `$value`.
  public function setEndKey($value, $encode = TRUE) {
    $this->options["endkey"] = $encode ? json_encode($value) : $value;
    return $this;
  }

  //@}


  //! @name First and Last Documents Identifiers
  //! @brief First and last documents to be included in the output.
  //! @details If you expect to have multiple documents emit identical keys, you'll need to use `startDocId` in
  //! addition to `startKey` to paginate correctly. The reason is that `startKey` alone will no longer be
  //! sufficient to uniquely identify a row. Those parameters are useless if you don't provide a `startKey`. In fact,
  //! CouchDB will first look at the `startKey` parameter, then it will use the `startDocId` parameter to further
  //! redefine the beginning of the range if multiple potential staring rows have the same key but different document IDs.
  //! Same thing for the `endDocId`.
  //@{


  //! @brief Sets the ID of the document with which to start the range.
  //! @param[in] string $value The ID of the document with which to start the range.
  public function setStartDocId($value) {
    $this->options["startkey_docid"] = $value;
    return $this;
  }

  //! @brief Sets the ID of the document with which to end the range.
  //! @param[in] string $value The ID of the document with which to end the range.
  public function setEndDocId($value) {
    $this->options["endkey_docid"] = $value;
    return $this;
  }

  //@}


  //! @brief Restricts the number of results.
  //! @details Allowed values: positive integers.
  //! @param[in] integer $value The maximum number of rows to include in the output.
  public function setLimit($value) {
    if (is_int($value) && $value > 0)
      $this->options["limit"] = $value;
    else
      throw new \Exception("\$value must be a positive integer.");

    return $this;
  }


  //! @brief Results should be grouped.
  //! @details The group option controls whether the reduce function reduces to a set of distinct keys or to a single
  //! result row. This will run the rereduce procedure. This parameter makes sense only if a reduce function is defined
  //! for the view.
  public function groupResults() {
    $this->options["group"] = "true";
    return $this;
  }


  //! @brief Level at which documents should be grouped.
  //! @details If your keys are JSON arrays, this parameter will specify how many elements in those arrays to use for
  //! grouping purposes. If your emitted keys are not JSON arrays this parameter's value will effectively be ignored.
  //! Allowed values: positive integers.
  //! @param[in] integer $value The number of elements used for grouping purposes.
  public function setGroupLevel($value) {
    if (is_int($value) && $value > 0) {
      $this->groupResults(); // This parameter is used only if 'group' is 'true'.
      $this->options["group_level"] = $value;
    }
    else
      throw new \Exception("\$value must be a positive integer.");

    return $this;
  }


  //! @brief Even is a reduce function is defined for the view, doesn't call it.
  //! @details If a view contains both a map and reduce function, querying that view will by default return the result
  //! of the reduce function. To avoid this behaviour you must call this method.
  public function doNotReduce() {
    $this->options["reduce"] = "false";
    return $this;
  }


  //! @brief Automatically fetches and includes full documents.
  //! @details However, the user should keep in mind that there is a race condition when using this option. It is
  //! possible that between reading the view data and fetching the corresponding document that the document has changed.
  //! @warning You can call this method only if the view doesn't contain a reduce function.
  public function includeDocs() {
    $this->options["include_docs"] = "true";
    return $this;
  }


  //! @brief Don't get any data, but all meta-data for this View. The number of documents in this View for example.
  public function excludeResults() {
    $this->options["limit"] = 0;
    return $this;
  }


  //! @brief Tells CouchDB to not include end key in the result.
  public function excludeEndKey() {
    $this->options["inclusive_end"] = "false";
    return $this;
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
    return $this;
  }


  //! @brief CouchDB will update the view after the query's result is returned.
  public function queryThenRefreshView() {
    $this->options["stale"] = "update_after";
    return $this;
  }

  //@}


  //! @brief Reverses order of results.
  //! @details Note that the descending option is applied before any key filtering, so you may need to swap the values
  //! of the start key and end key options to get the expected results.
  public function reverseOrderOfResults() {
    $this->options["descending"] = "true";
    return $this;
  }


  //! @brief Skips the defined number of documents.
  //! @details The skip option should only be used with small values, as skipping a large range of documents this way is
  //! inefficient (it scans the index from the start key and then skips N elements, but still needs to read all the index
  //! values to do that). For efficient paging you'll need to use start key and limit.
  //! Allowed values: positive integers.
  //! @param[in] integer $number The number of rows to skip.
  //! @exception Exception <c>Message: `\$number must be a positive integer.`</c>
  public function skipDocs($number) {
    if (is_int($number) && $number > 0)
      $this->options["skip"] = $number;
    else
      throw new \Exception("\$number must be a positive integer.");

    return $this;
  }


  //! @brief Includes conflict documents.
  public function includeConflicts() {
    $this->options["conflicts"] = "true";
    return $this;
  }


  //! @brief Includes an `update_seq` value indicating which sequence id of the database the view reflects.
  public function includeUpdateSeq() {
    $this->options['update_seq'] = "true";
    return $this;
  }


  //! @brief Includes all the rows, even if a match for a key is not found.
  public function includeMissingKeys() {
    $this->includeMissingKeys = TRUE;
    return $this;
  }


  //! @brief Returns `true` if includeMissingKeys() has been called before.
  public function issetIncludeMissingKeys() {
    return $this->includeMissingKeys;
  }

}