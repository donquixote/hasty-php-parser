<?php

namespace Donquixote\HastyPhpParser\Util;

use Donquixote\HastyPhpAst\Util\UtilBase;
use Donquixote\HastyPhpAst\Ast\Namespace_\AstNamespaceDeclaration;
use Donquixote\HastyPhpAst\Ast\Use_\AstUseStatement;
use Donquixote\HastyPhpParser\ParserStatic\Ptk_Qcn;
use Donquixote\HastyPhpParser\ParserStatic\Ptk_SkipWhitespaceAndComments;

final class ParserUtil extends UtilBase {

  /**
   * @param array $tokens
   * @param int $i
   *
   * @return string|int
   */
  static function nextSubstantialIncl(array $tokens, &$i) {
    while (TRUE) {
      $id = $tokens[$i][0];
      if (T_WHITESPACE !== $id && T_COMMENT !== $id && T_DOC_COMMENT !== $id) {
        return $id;
      }
      ++$i;
    }
    throw new \RuntimeException('Unreachable!');
  }

  /**
   * @param array $tokens
   * @param int $i
   *
   * @return string|int
   */
  static function nextSubstantialExcl(array $tokens, &$i) {
    ++$i;
    while (TRUE) {
      $id = $tokens[$i][0];
      if (T_WHITESPACE !== $id && T_COMMENT !== $id && T_DOC_COMMENT !== $id) {
        return $id;
      }
      ++$i;
    }
    throw new \RuntimeException('Unreachable!');
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *
   * @return string[]|bool
   */
  static function parseIdentifierList(array $tokens, &$i) {
    $list = array();
    while (TRUE) {
      $id = ParserUtil::nextSubstantialIncl($tokens, $i);
      if (T_STRING !== $id) {
        return FALSE;
      }
      $list[] = $tokens[$i][1];
      $id = ParserUtil::nextSubstantialExcl($tokens, $i);
      if (',' !== $id) {
        break;
      }
      ++$i;
    }
    return $list;
  }

  /**
   * Skips one instance of {..} curly braces, even if there are nested
   * sub-blocks.
   *
   * @param mixed[] $tokens
   * @param int $i
   *   Before: Position of the opening '{'.
   *   After (success): Position after the closing '}'.
   *   After (failure): Random position.
   *
   * @return null|false
   */
  static function skipCurvy(array $tokens, &$i) {
    $level = 0;
    for (++$i; ; ++$i) {
      if (')' === $token = $tokens[$i]) {
        if (--$level < 0) {
          ++$i;
          return NULL;
        }
      }
      elseif ('(' === $token) {
        ++$level;
      }
      elseif ('#' === $token) {
        --$i;
        return FALSE;
      }
    }

    throw new \RuntimeException('Unreachable code.');
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *   Before: Position of '{'.
   *   After (success): Position after '}'.
   *   After (Failure): Same as before.
   *
   * @return null|false
   */
  static function skipCurly(array $tokens, &$i) {
    $iStart = $i;
    $level = 0;
    for (++$i;; ++$i) {
      if ('}' === $token = $tokens[$i]) {
        if (--$level < 0) {
          ++$i;
          return NULL;
        }
      }
      elseif ('{' === $token || T_CURLY_OPEN === $token[0] || T_DOLLAR_OPEN_CURLY_BRACES === $token[0]) {
        ++$level;
      }
      elseif ('#' === $token) {
        $i = $iStart;
        return FALSE;
      }
    }

    throw new \RuntimeException('Unreachable code.');
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *   Before: Position on the T_NAMESPACE.
   *   After (success): Position after the ';'.
   *
   * @return bool|\Donquixote\HastyPhpAst\Ast\Namespace_\AstNamespaceDeclaration
   */
  static function parseNamespaceDeclaration(array $tokens, &$i) {
    $id = ParserUtil::nextSubstantialExcl($tokens, $i);
    if (T_STRING !== $id) {
      return FALSE;
    }
    $fqcn = Ptk_Qcn::parse($tokens, $i);
    if (FALSE === $fqcn) {
      return FALSE;
    }
    return new AstNamespaceDeclaration($fqcn);
  }

  /**
   * @param mixed[] $tokens
   * @param int $i
   *
   * @return \Donquixote\HastyPhpAst\Ast\Use_\AstUseStatement|false
   */
  static function parseUseStatementGroup(array $tokens, &$i) {
    $fqcnsByAlias = array();
    while (TRUE) {
      ++$i;
      Ptk_SkipWhitespaceAndComments::parse($tokens, $i);
      $fqcn = Ptk_Qcn::parse($tokens, $i);
      if (FALSE === $fqcn) {
        return FALSE;
      }
      Ptk_SkipWhitespaceAndComments::parse($tokens, $i);
      if (T_AS === $tokens[$i][0]) {
        ++$i;
        Ptk_SkipWhitespaceAndComments::parse($tokens, $i);
        if (T_STRING !== $tokens[$i][0]) {
          return FALSE;
        }
        $alias = $tokens[$i][1];
        ++$i;
        Ptk_SkipWhitespaceAndComments::parse($tokens, $i);
      }
      else {
        $alias = $fqcn->getName();
      }
      $fqcnsByAlias[$alias] = $fqcn;
      if (';' === $tokens[$i]) {
        ++$i;
        break;
      }
      elseif (',' !== $tokens[$i]) {
        return FALSE;
      }
    }
    return new AstUseStatement($fqcnsByAlias);
  }

}
