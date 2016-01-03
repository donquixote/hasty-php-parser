<?php

namespace Donquixote\HastyPhpParser\ParserStatic;

use Donquixote\HastyPhpAst\Name\Fqcn;

class Ptk_Qcn implements PtkParserStaticInterface {

  /**
   * @param mixed[] $tokens
   * @param int $i
   *   Before: Position of the first T_STRING of the qcn.
   *   After (success): Position after the last T_STRING of the qcn.
   *   After (failure): Random position!
   *
   * @return false|\Donquixote\HastyPhpAst\Name\FqcnInterface
   */
  public static function parse(array $tokens, &$i) {
    $qcnString = '';
    while (TRUE) {
      if (T_STRING !== $tokens[$i][0]) {
        return FALSE;
      }
      $qcnString .= $tokens[$i][1];
      ++$i;
      if (T_NS_SEPARATOR !== $tokens[$i][0]) {
        break;
      }
      $qcnString .= '\\';
      ++$i;
    }
    return Fqcn::createFromValidQcnString($qcnString);
  }
}
