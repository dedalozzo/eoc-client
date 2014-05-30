<?php

/**
 * @file LocalDoc.php
 * @brief This file contains the LocalDoc class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ElephantOnCouch\Doc;


/**
 * @brief Local document are not replicated and don't have attachments.
 * @details CouchDB uses local documents as replication checkpoints. You can use them to store, for example, local
 * configurations.\n
 * Inherit from LocalDoc if you want create a persistent class that is not replicable.
 * @nosubgrouping
   */
class LocalDoc extends AbstractDoc {


  /**
   * @brief Removes `_local/` from he document identifier.
   */
  protected function fixDocId() {
    if (isset($this->meta['_id']))
      $this->meta['_id'] = preg_replace('%\A_local/%m', "", $this->meta['_id']);
  }


  /**
   * @brief Gets the document path: `_local/`.
   * @return string
   */
  public function getPath() {
    return "_local/";
  }

}