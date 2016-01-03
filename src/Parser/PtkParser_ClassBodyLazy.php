<?php

namespace Donquixote\HastyPhpParser\Parser;

use Donquixote\HastyPhpAst\Ast\ClassLikeBody\AstClassLikeBody_LazyProxy;
use Donquixote\HastyPhpParser\Exception\ParseError;
use Donquixote\HastyPhpParser\Util\ParserUtil;

class PtkParser_ClassBodyLazy implements PtkParserInterface {

  /**
   * @var \Donquixote\HastyPhpParser\Parser\PtkParserInterface
   */
  private $decorated;

  /**
   * @return \Donquixote\HastyPhpParser\Parser\PtkParser_ClassBodyLazy
   */
  static function create() {
    return new self(PtkParser_ClassBody::create());
  }

  /**
   * @param \Donquixote\HastyPhpParser\Parser\PtkParserInterface $decorated
   */
  function __construct(PtkParserInterface $decorated) {
    $this->decorated = $decorated;
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
    if (FALSE === ParserUtil::skipCurly($tokens, $i)) {
      return FALSE;
    }

    return new AstClassLikeBody_LazyProxy($this->decorated, $tokens, $iStart);
  }
}
