<?php

//! @file FeedStyle.php
//! @brief This file contains the FeedStyle class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch\Enum;


//! @brief Feed Styles Enumerator.
//! @nosubgrouping
class FeedStyle extends \SplEnum {

  const __default = self::ALL_DOCS;

  //! @name Styles
  //@{
    const MAIN_ONLY = "main_only";
    const ALL_DOCS = "all_docs";
  //@}

}