<?php

//! @file DocPath.php
//! @brief This file contains the DocPath class.
//! @details
//! @author Filippo F. Fadda


//! @brief The CouchDB's enums namespace.
namespace ElephantOnCouch\Enum;


//! @brief Docucment Paths Enumerator.
//! @nosubgrouping
class DocPath extends \SplEnum {

  const __default = self::STD;

  //! @name Document Paths
  // @{
  const STD = ""; //!< Path for standard documents.
  const LOCAL = "_local/"; //!< Path for local documents.
  const DESIGN = "_design/"; //!< Path for design documents.
  //@}

}