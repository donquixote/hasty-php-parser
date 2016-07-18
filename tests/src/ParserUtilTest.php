<?php

namespace Donquixote\HastyPhpParser\Tests;

use Donquixote\HastyPhpParser\Util\ParserUtil;

class ParserUtilTest extends \PHPUnit_Framework_TestCase {

  /**
   * @param string $php
   * @param int $i_start
   * @param int $i_expected
   * @param bool $result_expected
   *
   * @dataProvider providerSkipCurvy
   */
  public function testSkipCurvy($php, $i_start, $i_expected, $result_expected) {

    $tokens = token_get_all($php);
    $tokens[] = '#';

    static::assertSame('(', $tokens[$i_start]);

    $i = $i_start;
    $result = ParserUtil::skipCurvy($tokens, $i);

    static::assertSame($i_expected, $i);

    if ($result_expected !== false) {
      static::assertSame(')', $tokens[$i_expected - 1]);
    }

    static::assertSame($result_expected, $result);
  }

  /**
   * @return mixed[][]
   *   Format: $[$dataset_name] = [$php, $i_expected, $result_expected]
   */
  public function providerSkipCurvy() {
    $php = <<<'EOT'
<?php

function foo($x, $y) {}

function bar('x', 7) {}

foo(" {$x} ($y{$z})");

if (foo()) {}

EOT;

    return [
      'foo' => [$php, 5, 11, null],
      'bar' => [$php, 18, 24, null],
      'call foo' => [$php, 29, 43, null],
      'if' => [$php, 47, 52, null],
    ];
  }

  /**
   * @param string $php
   * @param int $i_start
   * @param int $i_expected
   * @param bool $result_expected
   *
   * @dataProvider providerSkipCurly
   */
  public function testSkipCurly($php, $i_start, $i_expected, $result_expected) {

    $tokens = token_get_all($php);
    $tokens[] = '#';

    static::assertSame('{', $tokens[$i_start]);

    $i = $i_start;
    $result = ParserUtil::skipCurly($tokens, $i);

    static::assertSame($i_expected, $i);

    if ($result_expected !== false) {
      static::assertSame('}', $tokens[$i_expected - 1]);
    }

    static::assertSame($result_expected, $result);
  }

  /**
   * @return mixed[][]
   *   Format: $[$dataset_name] = [$php, $i_expected, $result_expected]
   */
  public function providerSkipCurly() {

    $php = <<<'EOT'
<?php

function foo() {
  if (true) {
    bar();
  }
}

function bar($x, $y) {
  return " $x {$y} ${$varname}" . ${$varname};
}

{}

EOT;

    return [
      'foo' => [$php, 8, 26, null],
      'bar' => [$php, 37, 63, null],
      '{}' => [$php, 64, 66, null],
    ];
  }

}
