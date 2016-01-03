<?php

namespace Donquixote\HastyPhpParser\Parser;

use Donquixote\HastyPhpParser\Exception\ParseError;

interface PtkParserInterface {

  /**
   * @param array $tokens
   *   The tokens from token_get_all()
   * @param int $i
   *   Before: Position of the first token of the element to parse.
   *   After, success: Position of the first token _after_ the parsed element.
   *   After, failure: Same as before.
   *
   * @return mixed|false|null
   *   FALSE, if this parser does not match.
   *   NULL, if the parsed element can be skipped in the result.
   *   A parse subtree, otherwise.
   *
   * @throws ParseError
   *   If a syntax error is found in the code.
   *
   * @see token_get_all()
   */
  function parse(array $tokens, &$i);

}
