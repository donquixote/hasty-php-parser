<?php

namespace Donquixote\HastyPhpParser\Parser;

use Donquixote\HastyPhpAst\Ast\File\AstFile;
use Donquixote\HastyPhpParser\Util\ParserUtil;

class PtkParser_File implements PtkParserInterface {

  /**
   * @var \Donquixote\HastyPhpParser\Parser\PtkParserInterface
   */
  private $parser;

  /**
   * @param bool $lazy
   *
   * @return \Donquixote\HastyPhpParser\Parser\PtkParser_File
   */
  static function create($lazy = FALSE) {
    return new self(PtkParser_ElementInFile::create($lazy));
  }


  /**
   * @param \Donquixote\HastyPhpParser\Parser\PtkParserInterface $parser
   */
  function __construct(PtkParserInterface $parser) {
    $this->parser = $parser;
  }

  /**
   * @param array $tokens
   * @param int $i
   *
   * @return \Donquixote\HastyPhpAst\Ast\File\AstFileInterface|false
   */
  function parse(array $tokens, &$i) {

    if (T_OPEN_TAG !== $tokens[$i][0]) {
      return FALSE;
    }

    ++$i;

    $nodes = array();
    while (TRUE) {
      $node = $this->parser->parse($tokens, $i);
      if (FALSE === $node) {
        $id = ParserUtil::nextSubstantialIncl($tokens, $i);
        if ('#' === $id) {
          // End of file/stream.
          break;
        }
        return FALSE;
      }
      if (NULL !== $node) {
        $nodes[] = $node;
      }
    }

    return new AstFile($nodes);
  }
}
