<?php

namespace Donquixote\HastyPhpParser\Tests;

use Donquixote\HastyPhpAst\Ast\ClassLike\AstClassLike;
use Donquixote\HastyPhpAst\Ast\ClassLike\AstClassLikeInterface;
use Donquixote\HastyPhpAst\Ast\File\AstFileInterface;
use Donquixote\HastyPhpAst\PhpToAst\PhpToAstInterface;
use Donquixote\HastyPhpParser\PhpToAst\PhpToAst_Parser;

class PhpToAstTest extends \PHPUnit_Framework_TestCase {

  /**
   * @param \Donquixote\HastyPhpAst\PhpToAst\PhpToAstInterface $phpToAst
   * @param string $class
   *
   * @dataProvider providerPhpToAst
   */
  function testPhpToAst(PhpToAstInterface $phpToAst, $class) {
    $reflectionClass = new \ReflectionClass($class);
    $file = $reflectionClass->getFileName();
    $php = file_get_contents($file);
    $fileAst = $phpToAst->phpGetAst($php);

    $classNodes = $this->fileAstGetClassNodes($fileAst);

    $this->assertEquals(array(0), array_keys($classNodes));

    $classNode = $classNodes[0];

    $this->assertEquals($reflectionClass->getShortName(), $classNode->getShortName());

    $this->assertEquals($reflectionClass->getDocComment(), $classNode->getDocComment());
  }

  /**
   * @param \Donquixote\HastyPhpAst\Ast\File\AstFileInterface $astFile
   *
   * @return \Donquixote\HastyPhpAst\Ast\ClassLike\AstClassLikeInterface[]
   */
  private function fileAstGetClassNodes(AstFileInterface $astFile) {
    $classNodes = array();
    foreach ($astFile->getNodes() as $astNode) {
      if ($astNode instanceof AstClassLikeInterface) {
        $classNodes[] = $astNode;
      }
    }
    return $classNodes;
  }

  /**
   * @return array[]
   */
  function providerPhpToAst() {
    $list = array();
    $classes = array(
      AstClassLike::class,
      AstClassLikeInterface::class,
    );
    foreach (array(
      PhpToAst_Parser::create(TRUE),
      PhpToAst_Parser::create(FALSE),
    ) as $phpToAst) {
      foreach ($classes as $class) {
        $list[] = array($phpToAst, $class);
      }
    }
    return $list;
  }

}


