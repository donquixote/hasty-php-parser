<?php

namespace Donquixote\HastyPhpParser\Parser;

trait OptionableTrait {

  /**
   * @var bool
   */
  private $required = TRUE;

  /**
   * @return $this
   */
  function makeOptional() {
    $this->required = FALSE;
    return $this;
  }

}
