<?php

namespace Donquixote\HastyPhpParser\Parser;

use Donquixote\HastyPhpAst\Ast\FunctionLike\AstFunctionLike;
use Donquixote\HastyPhpParser\Util\ParserUtil;

/**
 * Parses a function/method, but ignores both the parameters and the body.
 */
class PtkParser_FunctionLikeHeadOnly implements DeclarationParserInterface {

  /**
   * @param array $tokens
   *   The tokens from token_get_all()
   * @param int $iParent
   *   Before: Position on T_FUNCTION.
   *   After, success: Position directly after the closing '}' or ';'.
   *   After, failure: Same as before.
   * @param true[] $modifiers
   *   E.g. array(T_ABSTRACT => true, T_PRIVATE => true)
   * @param string $docComment
   *
   * @return false|\Donquixote\HastyPhpAst\Ast\FunctionLike\AstFunctionLike
   */
  function parse(array $tokens, &$iParent, array $modifiers = array(), $docComment = NULL) {
    $i = $iParent;
    $id = ParserUtil::nextSubstantialExcl($tokens, $i);
    if ('&' === $id) {
      $modifiers['&'] = TRUE;
      $id = ParserUtil::nextSubstantialExcl($tokens, $i);
    }
    if (T_STRING !== $id) {
      return FALSE;
    }
    $name = $tokens[$i][1];
    $id = ParserUtil::nextSubstantialExcl($tokens, $i);
    if ('(' !== $id) {
      return FALSE;
    }
    // Skip the signature.
    ParserUtil::skipCurvy($tokens, $i);
    $id = ParserUtil::nextSubstantialIncl($tokens, $i);
    if (';' === $id) {
      $iParent = $i + 1;
      return new AstFunctionLike($docComment, $modifiers, $name);
    }
    elseif ('{' === $id) {
      ParserUtil::skipCurly($tokens, $i);
      $iParent = $i;
      return new AstFunctionLike($docComment, $modifiers, $name);
    }
    else {
      return FALSE;
    }
  }
}
