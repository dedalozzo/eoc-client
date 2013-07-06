<?php

//! @file LocalDoc.php
//! @brief This file contains the LocalDoc class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Doc;


//! @brief Local document are not replicated and don't have attachments.
//! @details CouchDB uses local documents as replication checkpoints. You can use them to store, for example, local
//! configurations.<br />
//! Inherit from LocalDoc if you want create a persistent class that is not replicable.
//! @nosubgrouping
abstract class LocalDoc extends AbstractDoc {


  //! @brief Removes <i>_local/</i> from he document identifier.
  protected function fixDocId() {
    if (isset($this->meta[self::ID]))
      $this->meta[self::ID] = preg_replace('%\A_local/%m', "", $this->meta[self::ID]);
  }


  //! @brief Gets the document path: <i>_local/</i>.
  //! @return string
  public function getPath() {
    return "_local/";
  }

}