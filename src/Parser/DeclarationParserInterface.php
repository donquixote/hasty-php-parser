<?php

namespace Donquixote\HastyPhpParser\Parser;

use Donquixote\HastyPhpParser\Exception\ParseError;

/**
 * Specialized parser interface for declarations of functions, classes, and
 * class members.
 */
interface DeclarationParserInterface extends PtkParserInterface {

  /**
   * @param array $tokens
   *   The tokens from token_get_all()
   * @param int $i
   *   Before: Position of the T_FUNCTION or T_CLASS or T_VARIABLE.
   *   After, success: Position after the closing '}' or ';'.
   *   After, failure: Same as before.
   * @param true[] $modifiers
   *   E.g. array(T_ABSTRACT => true, T_INTERFACE => true, T_PRIVATE => true)
   * @param string $docComment
   *   Doc comment collected in calling code.
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
  function parse(array $tokens, &$i, array $modifiers = array(), $docComment = NULL);

}
