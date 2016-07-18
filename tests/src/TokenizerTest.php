<?php

namespace Donquixote\HastyPhpParser\Tests;

/**
 * Verify our understanding of token_get_all() behavior.
 */
class TokenizerTest extends \PHPUnit_Framework_TestCase {

  /**
   * @param string $php
   * @param mixed[] $tokens_expected
   *
   * @dataProvider providerTokenizer
   */
  public function testTokenizer($php, array $tokens_expected) {

    $tokens_actual = token_get_all("<?php\n\n" . $php);

    static::assertSame(
      [T_OPEN_TAG, "<?php\n", 1],
      array_shift($tokens_actual));

    static::assertSame(
      [T_WHITESPACE, "\n", 2],
      array_shift($tokens_actual));

    $this->assertSameTokens($tokens_expected, $tokens_actual);
  }

  /**
   * @return mixed[][]
   *   Format: $[$dataset_name] = [$php, $tokens_expected]
   */
  public function providerTokenizer() {
    return [
      'double-quoted string' => [
        '"123"',
        [
          [T_CONSTANT_ENCAPSED_STRING, '"123"', 3],
        ],
      ],
      'double-quoted string with vars' => [
        '"fit/{$w}x{$h}"',
        [
          '"',
          [T_ENCAPSED_AND_WHITESPACE, 'fit/', 3],
          [T_CURLY_OPEN, '{', 3],
          [T_VARIABLE, '$w', 3],
          '}',
          [T_ENCAPSED_AND_WHITESPACE, 'x', 3],
          [T_CURLY_OPEN, '{', 3],
          [T_VARIABLE, '$h', 3],
          '}',
          '"',
        ],
      ],
      'double-quoted string with vars II' => [
        '" {$w} {} {"',
        [
          '"',
          [T_ENCAPSED_AND_WHITESPACE, ' ', 3],
          [T_CURLY_OPEN, '{', 3],
          [T_VARIABLE, '$w', 3],
          '}',
          [T_ENCAPSED_AND_WHITESPACE, ' {} {', 3],
          '"',
        ],
      ],
      'heredoc' => [
        "<<<EOT\nxyz\nEOT;\n",
        [
          [T_START_HEREDOC, "<<<EOT\n", 3],
          [T_ENCAPSED_AND_WHITESPACE, "xyz\n", 4],
          [T_END_HEREDOC, 'EOT', 5],
          ';',
          [T_WHITESPACE, "\n", 5],
        ],
      ],
      'heredoc with vars' => [
        "<<<EOT\nxyz{\$x}\$a\nEOT;\n",
        [
          [T_START_HEREDOC, "<<<EOT\n", 3],
          [T_ENCAPSED_AND_WHITESPACE, 'xyz', 4],
          [T_CURLY_OPEN, '{', 4],
          [T_VARIABLE, '$x', 4],
          '}',
          [T_VARIABLE, '$a', 4],
          [T_ENCAPSED_AND_WHITESPACE, "\n", 4],
          [T_END_HEREDOC, 'EOT', 5],
          ';',
          [T_WHITESPACE, "\n", 5],
        ],
      ],
      'nowdoc' => [
        "<<<'EOT'\nxyz\nEOT;\n",
        [
          [T_START_HEREDOC, "<<<'EOT'\n", 3],
          [T_ENCAPSED_AND_WHITESPACE, "xyz\n", 4],
          [T_END_HEREDOC, 'EOT', 5],
          ';',
          [T_WHITESPACE, "\n", 5],
        ],
      ],
      'if' => [
        'if (true) {}',
        [
          [T_IF, 'if', 3],
          [T_WHITESPACE, ' ', 3],
          '(',
          [T_STRING, 'true', 3],
          ')',
          [T_WHITESPACE, ' ', 3],
          '{',
          '}',
        ],
      ],
      'php' => [
        <<<EOT
function foo(\$x, \$y) {
  return <<<EOTT
There are \$x or {\$y} cows.
EOTT;
}
EOT
        ,
        [
          [T_FUNCTION, 'function', 3],
          [T_WHITESPACE, ' ', 3],
          [T_STRING, 'foo', 3],
          '(',
          [T_VARIABLE, '$x', 3],
          ',',
          [T_WHITESPACE, ' ', 3],
          [T_VARIABLE, '$y', 3],
          ')',
          [T_WHITESPACE, ' ', 3],
          '{',
          [T_WHITESPACE, "\n" . '  ', 3],
          [T_RETURN, 'return', 4],
          [T_WHITESPACE, ' ', 4],
          [T_START_HEREDOC, '<<<EOTT' . "\n", 4],
          [T_ENCAPSED_AND_WHITESPACE, 'There are ', 5],
          [T_VARIABLE, '$x', 5],
          [T_ENCAPSED_AND_WHITESPACE, ' or ', 5],
          [T_CURLY_OPEN, '{', 5],
          [T_VARIABLE, '$y', 5],
          '}',
          [T_ENCAPSED_AND_WHITESPACE, ' cows.' . "\n", 5],
          [T_END_HEREDOC, 'EOTT', 6],
          ';',
          [T_WHITESPACE, "\n", 6],
          '}',
        ],
      ],
      [
        '{}',
        [
          '{',
          '}',
        ],
      ],
      [
        '${$x}',
        [
          '$',
          '{',
          [T_VARIABLE, '$x', 3],
          '}',
        ],
      ],
      [
        '"{$x}"',
        [
          '"',
          [T_CURLY_OPEN, '{', 3],
          [T_VARIABLE, '$x', 3],
          '}',
          '"',
        ],
      ],
      [
        '"${$x}"',
        [
          '"',
          [T_DOLLAR_OPEN_CURLY_BRACES, '${', 3],
          [T_VARIABLE, '$x', 3],
          '}',
          '"',
        ],
      ],
      [
        '"{${$x}}"',
        [
          '"',
          [T_CURLY_OPEN, '{', 3],
          '$',
          '{',
          [T_VARIABLE, '$x', 3],
          '}',
          '}',
          '"',
        ],
      ],
    ];
  }

  /**
   * @param array $tokens_expected
   * @param array $tokens_actual
   */
  private function assertSameTokens(array $tokens_expected, array $tokens_actual) {

    static::assertSame(
      $this->transformTokens($tokens_expected),
      $this->transformTokens($tokens_actual));

    static::assertSame(
      $tokens_expected,
      $tokens_actual);
  }

  /**
   * @param array $tokens
   *
   * @return array
   */
  private function transformTokens(array $tokens) {
    $rows = [];
    foreach ($tokens as $token) {
      if (is_array($token)) {
        $cells = [];
        foreach ($token as $i => $cell) {
          if ($i === 0) {
            $cells[] = token_name($cell);
          }
          elseif ($i === 1) {
            $cells[] = $this->exportSnippet($cell);
          }
          else {
            $cells[] = var_export($cell, TRUE);
          }
        }
        $rows[] = '[' . implode(', ', $cells) . '],';
      }
      else {
        $rows[] = var_export($token, TRUE) . ',';
      }
    }
    return implode("\n", $rows);
  }

  /**
   * @param string $snippet
   *
   * @return string
   */
  private function exportSnippet($snippet) {

    if ($snippet === '') {
      return "''";
    }

    $pieces = [];
    foreach (explode("\n", $snippet) as $line) {
      if ($line !== '') {
        $pieces[] = var_export($line, TRUE);
      }
      $pieces[] = TRUE;
    }
    array_pop($pieces);

    $pieces_combined = [];
    $glue = '';
    foreach ($pieces as $piece) {
      if (TRUE === $piece) {
        $glue .= '\n';
      }
      else {
        if ('' !== $glue) {
          $pieces_combined[] = '"' . $glue . '"';
          $glue = '';
        }
        $pieces_combined[] = $piece;
      }
    }

    if ('' !== $glue) {
      $pieces_combined[] = '"' . $glue . '"';
    }

    return implode(' . ', $pieces_combined);
  }
}
