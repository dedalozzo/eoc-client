<?php

//! @file DocOpts.php
//! @brief This file contains the DocOpts class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief TODO
class DocOpts {
  private $options = array();


  //! @brief Resets the options.
  //! @return NULL.
  public function reset() {
    unset($this->options);
    $this->options = array();
  }


  //! @brief Returns an associative array of the chosen options. Used internally by <i>src.getDoc()</i> method.
  //! @return An associative array.
  public function asArray() {
    return $this->options;
  }


  //! @brief Includes information about the encoding of each document's attachments.
  //! @return NULL.
  public function includeAttEncodingInfo() {
    $this->options['att_encoding_info'] = 'true';
  }


  //! @brief Includes a metadata structure that holds information about all the document's attachments.
  //! @return NULL.
  public function includeAttachments() {
    $this->options['attachments'] = 'true';
  }


  //! @brief Include just the changed attachments from the specified revision(s).
  //! @details If you already have a local copy of an earlier revision of a document and its attachments, you may want
  //! to fetch only the attachments that have changed since a particular revision. You can specify one or more revision IDs.
  //! In the last case you must use an array of string. The response will include the content of only those attachments
  //! that changed since the given revision(s).
  //! @param[in] string|array $revs The revision(s) identifier(s).
  //! @return NULL.
  public function includeAttsSince(array $revs) {
    $this->options['atts_since'] = json_encode($revs);
  }


  //! @brief Includes information about document's conflicts.
  //! @return NULL.
  public function includeConflicts() {
    $this->options['conflicts'] = 'true';
  }


  //! @brief Includes information about deleted document's conflicts.
  //! @return NULL.
  public function includeDeletedConflicts() {
    $this->options['deleted_conflicts'] = 'true';
  }


  //! @brief Includes the sequence number of the revision in the database.
  //! @return NULL.
  public function includeLocalSeq() {
    $this->options['local_seq'] = 'true';
  }


  //! @brief Equals to calling includeRevsInfo(), includeConflicts() and includeDeletedConflicts().
  //! @return NULL.
  public function includeMeta() {
    $this->options['meta'] = 'true';
  }


  //! @brief This can be used alternatively to includeRevsInfo() method. This option is used by the replicator to return
  //! an array of revision IDs more efficiently. The numeric prefixes are removed, with a "start" value indicating the
  //! prefix for the first (most recent) ID.
  //! @details CouchDB will return something like this:
  //!          {
  //!            "_revisions": {
  //!              "start": 3,
  //!              "ids": ["fffff", "eeeee", "ddddd"]
  //!            }
  //!          }
  //! @return NULL.
  public function includeRevs() {
    $this->options['revs'] = 'true';
  }


  //! @brief Includes information about all the document's revisions.
  //! @details CouchDB will return something like this:
  //!          {
  //!            "_revs_info": [
  //!              {"rev": "3-ffffff", "status": "available"},
  //!              {"rev": "2-eeeeee", "status": "missing"},
  //!              {"rev": "1-dddddd", "status": "deleted"},
  //!            ]
  //!          }
  //! @return NULL.
  public function includeRevsInfo() {
    $this->options['revs_info'] = 'true';
  }


  //! @brief You can fetch the bodies of multiple revisions at once using this option. Using <b>$revs = 'all'</b> you can
  //! fetch all leaf revisions; alternatively you can specify an array of revisions. The JSON returns an array of objects
  //! with an "ok" key pointing to the document, or a "missing" key pointing to the rev string.
  //! @details CouchDB will return something like this:
  //! [{"missing":"1-fbd8a6da4d669ae4b909fcdb42bb2bfd"},{"ok":{"_id":"test","_rev":"2-5bc3c6319edf62d4c624277fdd0ae191","hello":"foo"}}]
  //! @return NULL.
  public function includeOpenRevs($revs = 'all') {
    if (is_array($revs))
      $this->options['open_revs'] = json_encode($revs);
    else
      $this->options['open_revs'] = 'all';
  }


  //! @brief The option is supposed to tell the source to send the leafs of an edit branch containing a requested
  //! revision, i.e. if the document has been updated since the revlist was calculated, the source is free to send the
  //! new one.
  //! @return NULL.
  public function includeLatest() {
    $this->options['latest'] = 'true';
  }

}

?>
