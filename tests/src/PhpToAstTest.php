<?php

namespace Donquixote\HastyPhpParser\Tests;

use Donquixote\HastyPhpAst\PhpToAst\PhpToAstInterface;
use Donquixote\HastyPhpParser\PhpToAst\PhpToAst_Parser;

class PhpToAstTest extends \PHPUnit_Framework_TestCase {

  /**
   * @param \Donquixote\HastyPhpAst\PhpToAst\PhpToAstInterface $phpToAst
   * @param string $class
   * @param string $export
   *
   * @dataProvider providerPhpToAst
   */
  function testPhpToAst(PhpToAstInterface $phpToAst, $class, $export) {
    $reflectionClass = new \ReflectionClass($class);
    $file = $reflectionClass->getFileName();
    $php = file_get_contents($file);
    $fileAst = $phpToAst->phpGetAst($php);
    $this->assertEquals($export, var_export($fileAst, TRUE));
  }

  /**
   * @return array[]
   */
  function providerPhpToAst() {
    $list = array();
    $classes = array();
    foreach (scandir($dir = dirname(__DIR__) . '/fixtures') as $candidate) {
      if ('.' === $candidate[0]) {
        continue;
      }
      $fragments = explode('.', $candidate);
      $ext = array_pop($fragments);
      if ('txt' !== $ext) {
        continue;
      }
      $classes[implode('\\', $fragments)] = file_get_contents($dir . '/' . $candidate);
    }
    foreach (array(
      PhpToAst_Parser::create(TRUE),
      PhpToAst_Parser::create(FALSE)
    ) as $phpToAst) {
      foreach ($classes as $class => $export) {
        $list[] = array($phpToAst, $class, $export);
      }
    }
    return $list;
  }

}


