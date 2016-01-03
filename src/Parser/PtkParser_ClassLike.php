<?php

namespace Donquixote\HastyPhpParser\Parser;

use Donquixote\HastyPhpAst\Ast\ClassLike\AstClassLike;
use Donquixote\HastyPhpParser\Exception\ParseError;
use Donquixote\HastyPhpParser\Util\ParserUtil;

class PtkParser_ClassLike implements DeclarationParserInterface {

  /**
   * @var \Donquixote\HastyPhpParser\Parser\PtkParserInterface
   */
  private $classBodyParser;

  /**
   * @param bool $lazy
   *
   * @return \Donquixote\HastyPhpParser\Parser\PtkParser_ClassLike
   */
  static function create($lazy = FALSE) {
    return $lazy
      ? new self(PtkParser_ClassBodyLazy::create())
      : new self(PtkParser_ClassBody::create());
  }

  /**
   * @param \Donquixote\HastyPhpParser\Parser\PtkParserInterface $classBodyParser
   */
  function __construct(PtkParserInterface $classBodyParser) {
    $this->classBodyParser = $classBodyParser;
  }

  /**
   * @param array $tokens
   *   The tokens from token_get_all()
   * @param int $iParent
   *   Before: Position of the T_CLASS or T_INTERFACE or T_TRAIT.
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
  function parse(array $tokens, &$iParent, array $modifiers = array(), $docComment = NULL) {
    $type = $tokens[$iParent][0];
    $i = $iParent;
    $id = ParserUtil::nextSubstantialExcl($tokens, $i);
    if (T_STRING !== $id) {
      return FALSE;
    }
    $name = $tokens[$i][1];
    $id = ParserUtil::nextSubstantialExcl($tokens, $i);
    $extends = array();
    if (T_EXTENDS === $id) {
      ++$i;
      $extends = ParserUtil::parseIdentifierList($tokens, $i);
      if (FALSE === $extends) {
        return FALSE;
      }
      $id = ParserUtil::nextSubstantialIncl($tokens, $i);
    }
    $implements = array();
    if (T_IMPLEMENTS === $id) {
      ++$i;
      $implements = ParserUtil::parseIdentifierList($tokens, $i);
      if (FALSE === $implements) {
        return FALSE;
      }
      $id = ParserUtil::nextSubstantialIncl($tokens, $i);
    }
    if ('{' !== $id) {
      return FALSE;
    }
    $body = $this->classBodyParser->parse($tokens, $i);
    if (FALSE === $body) {
      return FALSE;
    }
    $iParent = $i;
    if (T_CLASS === $type) {
      $parentClassName = isset($extends[0]) ? $extends[0] : NULL;
      return new AstClassLike($docComment, $modifiers, $name, $parentClassName, $implements, $body);
    }
    elseif (T_INTERFACE === $type) {
      return new AstClassLike($docComment, $modifiers, $name, NULL, $extends, $body);
    }
    else {
      return new AstClassLike($docComment, $modifiers, $name, NULL, array(), $body);
    }
  }
}
