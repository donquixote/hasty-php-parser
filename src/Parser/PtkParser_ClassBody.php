<?php

namespace Donquixote\HastyPhpParser\Parser;

use Donquixote\HastyPhpAst\Ast\ClassLikeBody\AstClassLikeBody;
use Donquixote\HastyPhpParser\Util\ParserUtil;

class PtkParser_ClassBody implements PtkParserInterface {

  /**
   * @var \Donquixote\HastyPhpParser\Parser\PtkParserInterface
   */
  private $memberParser;

  /**
   * @return \Donquixote\HastyPhpParser\Parser\PtkParser_ClassBody
   */
  static function create() {
    return new self(PtkParser_ClassMember::create());
  }

  /**
   * @param \Donquixote\HastyPhpParser\Parser\PtkParserInterface $memberParser
   */
  function __construct(PtkParserInterface $memberParser) {
    $this->memberParser = $memberParser;
  }

  /**
   * @param mixed[] $tokens
   *   Tokens from token_get_all().
   * @param int $i
   *   Before: Position of the opening '{'.
   *   After (success): Position after the closing '}'.
   *   After (failure): Same as before.
   *
   * @return \Donquixote\HastyPhpAst\Ast\ClassLikeBody\AstClassLikeBodyInterface|false
   */
  function parse(array $tokens, &$i) {
    $iStart = $i;

    if ('{' !== $tokens[$i]) {
      # return FALSE;
      // Wrong usage of this parser. The calling code is responsible!
      throw new \InvalidArgumentException('Class body must begin with "{".');
    }

    ++$i;

    $memberNodes = array();
    while (TRUE) {
      $memberNode = $this->memberParser->parse($tokens, $i);
      if (FALSE === $memberNode) {
        $id = ParserUtil::nextSubstantialIncl($tokens, $i);
        if ('}' === $id) {
          // End of class body.
          ++$i;
          break;
        }
        $i = $iStart;
        return FALSE;
      }
      if (NULL !== $memberNode) {
        $memberNodes[] = $memberNode;
      }
    }

    return new AstClassLikeBody($memberNodes);
  }
}
