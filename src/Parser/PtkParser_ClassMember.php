<?php

namespace Donquixote\HastyPhpParser\Parser;

use Donquixote\HastyPhpAst\Ast\UseTrait\AstUseTrait;
use Donquixote\HastyPhpParser\Exception\ParseError;
use Donquixote\HastyPhpParser\Util\ParserUtil;

class PtkParser_ClassMember implements PtkParserInterface {

  /**
   * @var \Donquixote\HastyPhpParser\Parser\DeclarationParserInterface
   */
  private $methodParser;

  /**
   * @return \Donquixote\HastyPhpParser\Parser\PtkParser_ClassMember
   */
  static function create() {
    return new self(new PtkParser_FunctionLikeHeadOnly());
  }

  /**
   * @param \Donquixote\HastyPhpParser\Parser\DeclarationParserInterface $methodParser
   */
  function __construct(DeclarationParserInterface $methodParser) {
    $this->methodParser = $methodParser;
  }

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
  function parse(array $tokens, &$i) {
    $iStart = $i;
    $result = $this->doParse($tokens, $i);
    if (FALSE === $result) {
      $i = $iStart;
      return FALSE;
    }
    return $result;
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *
   * @return mixed
   */
  private function doParse(array $tokens, &$i) {

    $docComment = NULL;
    $modifiers = array();

    for (;; ++$i) {

      $id = $tokens[$i][0];

      if (T_WHITESPACE === $id || T_COMMENT === $id) {
        // Continue loop.
      }
      elseif (T_DOC_COMMENT === $id) {
        $docComment = $tokens[$i][1];
        // Continue loop.
      }
      elseif (T_PROTECTED === $id || T_PRIVATE === $id || T_PUBLIC === $id || T_ABSTRACT === $id || T_FINAL === $id || T_STATIC === $id) {
        $modifiers[$id] = TRUE;
        // Continue loop.
      }
      elseif (T_FUNCTION === $id) {
        return $this->methodParser->parse($tokens, $i, $modifiers, $docComment);
      }
      elseif (FALSE && T_USE === $id) {
        return self::parseUseTrait($tokens, $i);
      }
      elseif (FALSE && T_VARIABLE === $id) {
        return self::parseMemberVariable($tokens, $i, $modifiers, $docComment);
      }
      elseif (FALSE && T_CONST === $id) {
        return self::parseClassConstant($tokens, $i, $modifiers, $docComment);
      }
      else {
        return self::ignoreMiraculousMember($tokens, $i);
      }
    }

    throw new \RuntimeException('Unreachable code.');
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *   Before: Position of T_TRAIT
   *   After (success): Position after the closing ';' or '}'.
   *   After (failure): Random position.
   *
   * @return \Donquixote\HastyPhpAst\Ast\UseTrait\AstUseTrait|false
   */
  static function parseUseTrait(array $tokens, &$i) {
    ++$i;
    $names = ParserUtil::parseIdentifierList($tokens, $i);
    if (FALSE === $names) {
      return FALSE;
    }
    $id = ParserUtil::nextSubstantialIncl($tokens, $i);
    if (';' === $id) {
      ++$i;
      return new AstUseTrait($names);
    }
    elseif ('{' === $id) {
      ParserUtil::skipCurly($tokens, $i);
      return new AstUseTrait($names);
    }
    else {
      return FALSE;
    }
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *   Before: Position of T_CONST
   *   After (success): Position after the closing ';' or '}'.
   *   After (failure): Random position.
   * @param true[] $modifiers
   *   E.g. array(T_ABSTRACT => true, T_INTERFACE => true, T_PRIVATE => true)
   * @param string $docComment
   *   Doc comment collected in calling code.
   *
   * @return bool|null
   */
  static function parseClassConstant(array $tokens, &$i, array $modifiers = array(), $docComment = NULL) {
    // @todo Not implemented.
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *   Before: Position of T_VARIABLE
   *   After (success): Position after the closing ';'.
   *   After (failure): Random position.
   * @param true[] $modifiers
   *   E.g. array(T_ABSTRACT => true, T_INTERFACE => true, T_PRIVATE => true)
   * @param string $docComment
   *   Doc comment collected in calling code.
   *
   * @return bool|null
   */
  static function parseMemberVariable(array $tokens, &$i, array $modifiers = array(), $docComment = NULL) {
    // @todo Not implemented.
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *
   * @return bool|null
   */
  static function ignoreMiraculousMember(array $tokens, &$i) {

    # $iStart = $i;

    for (;; ++$i) {
      if (';' === $token = $tokens[$i]) {
        ++$i;
        return NULL;
        # return new AstIgnored($tokens, $iStart, $i);
      }
      elseif ('(' === $token) {
        if (FALSE === ParserUtil::skipCurvy($tokens, $i)) {
          return FALSE;
        }
        --$i;
      }
      elseif ('{' === $token) {
        if (FALSE === ParserUtil::skipCurly($tokens, $i)) {
          return FALSE;
        }
        return NULL;
        # return new AstIgnored($tokens, $iStart, $i);
      }
      elseif (')' === $token) {
        ++$i;
        return FALSE;
      }
      elseif ('}' === $token) {
        ++$i;
        return FALSE;
      }
      elseif ('#' === $token) {
        return FALSE;
      }
    };

    return FALSE;
  }
}
