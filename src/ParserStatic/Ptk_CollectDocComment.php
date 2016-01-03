<?php

namespace Donquixote\HastyPhpParser\ParserStatic;



/**
 * A parser that collects the doc comment for the subsequent definition.
 */
class Ptk_CollectDocComment implements PtkParserStaticInterface {

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
   * @see token_get_all()
   */
  static function parse(array $tokens, &$i) {
    $docComment = NULL;
    for (;; ++$i) {
      // For string-valued tokens, $token[0] is a string/char.
      // For array-valued tokens, $token[0] is an integer.
      $id = $tokens[$i][0];
      if (T_DOC_COMMENT === $id) {
        $docComment = $tokens[$i][1];
      }
      elseif (T_COMMENT === $id) {
        $docComment = NULL;
      }
      elseif (T_WHITESPACE === $id) {
        // Unset $docComment based on blank lines?
      }
      else {
        break;
      }
    }
    return $docComment;
  }
}
