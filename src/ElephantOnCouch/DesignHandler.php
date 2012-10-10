<?php

//! @file DesignHandler.php
//! @brief This file contains the DesignHandler class.
//! @details
//! @author Filippo F. Fadda


namespace ElephantOnCouch;


//! @brief TODO
abstract class DesignHandler {
  use Properties;

  private $name;

  protected $section;


  //! @brief Returns the handler as an array.
  abstract public function asArray();


  //! @brief Handler constructor.
  public function __construct($name) {
    $this->name = $name;
  }


  //! @brief TODO
  protected function checkSyntax($code) {
    // We add the PHP tags, else the lint ignores the code. The PHP command line option -r doesn't work.
    $code = "<?php ".$code." ?>";

    // Try to create a temporary physical file. The function 'proc_open' doesn't allow to use a memory file.
    if ($fd = fopen("php://temp", "r+")) {
      fputs($fd, $code); // Writes the message body.
      // We don't need to flush because we call rewind.
      rewind($fd); // Sets the pointer to the beginning of the file stream.

      $dspec = array(
        $fd,
        1 => array('pipe', 'w'), // stdout
        2 => array('pipe', 'w'), // stderr
      );

      $proc = proc_open(PHP_BINARY." -l", $dspec, $pipes);

      if (is_resource($proc)) {
        // Reads the stdout output.
        $output = "";
        while (!feof($pipes[1])) {
          $output .= fgets($pipes[1]);
        }

        // Reads the stderr output.
        $error = "";
        while (!feof($pipes[2])) {
          $error .= fgets($pipes[2]);
        }

        // Free all resources.
        fclose($fd);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        if (preg_match("/\ANo syntax errors/", $output) === 0) {
          $pattern = array("/\APHP Parse error:  /",
                           "/in - /",
                           "/\z -\n/");

          $error = ucfirst(preg_replace($pattern, "", $error));

          throw new \Exception($error);
        }
      }
      else
        throw new \Exception("Cannot execute the 'PHP -l' command.");
    }
    else
      throw new \Exception("Cannot create the temporary file.");
  }


  public function getName() {
    return $this->name;
  }


  public function getSection() {
    return $this->section;
  }

}

?>