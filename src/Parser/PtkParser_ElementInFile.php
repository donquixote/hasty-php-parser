<?php

namespace Donquixote\HastyPhpParser\Parser;

use Donquixote\HastyPhpAst\Ast\ClassLikeBody\AstClassLikeBody_LazyProxy;
use Donquixote\HastyPhpParser\Exception\ParseError;
use Donquixote\HastyPhpParser\Util\ParserUtil;

class PtkParser_ElementInFile implements PtkParserInterface {

  /**
   * @var \Donquixote\HastyPhpParser\Parser\DeclarationParserInterface
   */
  private $classLikeParser;

  /**
   * @param bool $lazy
   *
   * @return \Donquixote\HastyPhpParser\Parser\PtkParser_ElementInFile
   */
  static function create($lazy = FALSE) {
    return new self(PtkParser_ClassLike::create($lazy));
  }

  /**
   * @param \Donquixote\HastyPhpParser\Parser\DeclarationParserInterface $classLikeParser
   */
  function __construct(
    DeclarationParserInterface $classLikeParser
  ) {
    $this->classLikeParser = $classLikeParser;
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

    $id = ParserUtil::nextSubstantialIncl($tokens, $i);

    if (T_USE === $id) {
      return ParserUtil::parseUseStatementGroup($tokens, $i);
    }
    elseif (T_NAMESPACE === $id) {
      return ParserUtil::parseNamespaceDeclaration($tokens, $i);
    }
    elseif (T_FINAL === $id || T_ABSTRACT === $id) {
      if (T_DOC_COMMENT === $tokens[$i - 2][0]) {
        $docComment = $tokens[$i - 2][1];
      }
      else {
        $docComment = NULL;
      }
      $modifiers = array($id => TRUE);
      while (TRUE) {
        $id = ParserUtil::nextSubstantialExcl($tokens, $i);
        $modifiers[$id] = TRUE;
        if (T_CLASS === $id || T_INTERFACE === $id || T_TRAIT === $id) {
          return $this->classLikeParser->parse($tokens, $i, $modifiers, $docComment);
        }
        elseif (T_FINAL === $id || T_ABSTRACT === $id) {
          $modifiers[$id] = TRUE;
        }
        else {
          // Something other than the above does not belong here.
          return FALSE;
        }
      }
      throw new \RuntimeException('Unreachable code.');
    }
    elseif (T_CLASS === $id || T_INTERFACE === $id || T_TRAIT === $id) {
      if (T_DOC_COMMENT === $tokens[$i - 2][0]) {
        $docComment = $tokens[$i - 2][1];
      }
      else {
        $docComment = NULL;
      }
      return $this->classLikeParser->parse($tokens, $i, array($id => TRUE), $docComment);
    }
    elseif ('#' === $id) {
      return FALSE;
    }
    else {
      return self::ignoreStatement($tokens, $i);
    }
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *
   * @return bool|\Donquixote\HastyPhpAst\Ast\ClassLikeBody\AstClassLikeBody_LazyProxy
   */
  static function parseClassBody(array $tokens, &$i) {
    $iStart = $i;
    if (FALSE === ParserUtil::skipCurly($tokens, $i)) {
      return FALSE;
    }

    return AstClassLikeBody_LazyProxy::create($tokens, $iStart);
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *
   * @return bool|null
   */
  static function ignoreStatement($tokens, &$i) {

    for (;; ++$i) {
      if (';' === $token = $tokens[$i]) {
        ++$i;
        return NULL;
      }
      elseif ('(' === $token) {
        if (FALSE === ParserUtil::skipCurvy($tokens, $i)) {
          return FALSE;
        }
      }
      elseif ('{' === $token) {
        if (FALSE === ParserUtil::skipCurly($tokens, $i)) {
          return FALSE;
        }
        return NULL;
      }
      elseif (')' === $token) {
        return FALSE;
      }
      elseif ('}' === $token) {
        return FALSE;
      }
      elseif ('#' === $token) {
        return FALSE;
      }
    }

    return FALSE;
  }

}
