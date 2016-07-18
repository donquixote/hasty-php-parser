<?php

namespace Donquixote\HastyPhpParser\PhpToAst;

use Donquixote\HastyPhpAst\Ast\File\AstFileInterface;
use Donquixote\HastyPhpAst\PhpToAst\PhpToAstInterface;
use Donquixote\HastyPhpParser\Exception\ParseError;
use Donquixote\HastyPhpParser\Parser\PtkParser_File;
use Donquixote\HastyPhpParser\Parser\PtkParserInterface;

class PhpToAst_Parser implements PhpToAstInterface {

  /**
   * @var \Donquixote\HastyPhpParser\Parser\PtkParserInterface
   */
  private $parser;

  /**
   * @param bool $lazy
   *
   * @return \Donquixote\HastyPhpParser\PhpToAst\PhpToAst_Parser
   */
  static function create($lazy = FALSE) {
    return new self(PtkParser_File::create($lazy));
  }

  /**
   * @param \Donquixote\HastyPhpParser\Parser\PtkParserInterface $parser
   */
  function __construct(PtkParserInterface $parser) {
    $this->parser = $parser;
  }

  /**
   * @param string $php
   *   PHP code read from a file.
   *
   * @return \Donquixote\HastyPhpAst\Ast\File\AstFileInterface|null
   */
  function phpGetAst($php) {

    $tokens = token_get_all($php);
    $tokens[] = '#';
    $i = 0;

    try {
      $fileAst = $this->parser->parse($tokens, $i);
    }
    catch (ParseError $e) {
      return NULL;
    }

    if (!$fileAst instanceof AstFileInterface) {
      return NULL;
    }

    return $fileAst;
  }
}
