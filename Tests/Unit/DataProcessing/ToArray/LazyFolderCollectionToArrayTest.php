<?php

declare(strict_types=1);

/*
 * This file is part of the "nb_headless_content_blocks" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Unit\Testing\UnitTestCase;

class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function dataProviderGetPaths(): array
    {
        return [
            'empty array' => [
                'input' => [],
                'expected' => [],
            ],
            'null input' => [
                'input' => null,
                'expected' => [],
            ],
            'invalid type' => [
                'input' => 'invalid',
                'expected' => [],
            ],
            'integer input' => [
                'input' => 123,
                'expected' => [],
            ],
            'string input' => [
                'input' => 'string',
                'expected' => [],
            ],
            'boolean input' => [
                'input' => true,
                'expected' => [],
            ],
            'float input' => [
                'input' => 123.45,
                'expected' => [],
            ],
            'object input' => [
                'input' => new \stdClass(),
                'expected' => [],
            ],
            'array with null values' => [
                'input' => [null, null, null],
                'expected' => [],
            ],
            'array with empty arrays' => [
                'input' => [[], [], []],
                'expected' => [],
            ],
            'array with empty strings' => [
                'input' => ['', '', ''],
                'expected' => [],
            ],
            'array with numeric values' => [
                'input' => [1, 2, 3],
                'expected' => [],
            ],
            'array with mixed types' => [
                'input' => [1, 'two', true, 3.14],
                'expected' => [],
            ],
            'array with objects' => [
                'input' => [(object)['foo' => 'bar'], (object)['baz' => 'qux']],
                'expected' => [],
            ],
            'array with integers' => [
                'input' => [1, 2, 3],
                'expected' => [],
            ],
            'array with floats' => [
                'input' => [1.1, 2.2, 3.3],
                'expected' => [],
            ],
            'array with booleans' => [
                'input' => [true, false, true],
                'expected' => [],
            ],
            'array with strings' => [
                'input' => ['a', 'b', 'c'],
                'expected' => [],
            ],
            'array with nulls and values' => [
                'input' => [null, 'value', null, 123, null],
                'expected' => [],
            ],
            'array with empty strings and values' => [
                'input' => ['', 'value', '', 123, ''],
                'expected' => [],
            ],
            'array with numeric strings' => [
                'input' => ['1', '2', '3'],
                'expected' => [],
            ],
            'array with unicode' => [
                'input' => ['🎉', '🚀', '💻'],
                'expected' => [],
            ],
            'array with special characters' => [
                'input' => ['<script>alert(1)</script>', 'path/with spaces', 'path\\with backslash'],
                'expected' => [],
            ],
            'array with newlines' => [
                'input' => ['line1\nline2', 'tab\there', 'carriage\rreturn'],
                'expected' => [],
            ],
            'array with whitespace' => [
                'input' => ['  ', "\t", "\n", "\r"],
                'expected' => [],
            ],
            'array with very long strings' => [
                'input' => [str_repeat('x', 1000)],
                'expected' => [],
            ],
            'array with very long paths' => [
                'input' => [str_repeat('path/', 100)],
                'expected' => [],
            ],
            'array with deep nesting' => [
                'input' => [[[[[[[]]]]]]],
                'expected' => [],
            ],
            'array with mixed nesting' => [
                'input' => [[[[[]]], [[[]]], [[]]], [[[[[]]]]], [[[[[]]]]]],
                'expected' => [],
            ],
            'array with associative keys' => [
                'input' => ['key1' => 'value1', 'key2' => 'value2'],
                'expected' => [],
            ],
            'array with numeric keys' => [
                'input' => [0 => 'value0', 1 => 'value1', 2 => 'value2'],
                'expected' => [],
            ],
            'array with mixed keys' => [
                'input' => [0 => 'a', 'b' => 'b', 2 => 'c'],
                'expected' => [],
            ],
            'array with null keys' => [
                'input' => [null => 'value', 0 => 'zero'],
                'expected' => [],
            ],
            'array with empty associative keys' => [
                'input' => ['', 'empty', 'value'],
                'expected' => [],
            ],
            'array with unicode keys' => [
                'input' => ['🎉' => 'value', '🚀' => 'value2'],
                'expected' => [],
            ],
            'array with special char keys' => [
                'input' => ['<script>' => 'value', 'path/' => 'value2'],
                'expected' => [],
            ],
            'array with mixed unicode keys' => [
                'input' => ['🎉' => 'value', '123' => 'value2', 'test' => 'value3'],
                'expected' => [],
            ],
            'array with numeric and string keys' => [
                'input' => [123 => 'value', 'string' => 'value2', 456 => 'value3'],
                'expected' => [],
            ],
            'array with negative numeric keys' => [
                'input' => [-1 => 'value', -2 => 'value2'],
                'expected' => [],
            ],
            'array with float keys' => [
                'input' => [1.5 => 'value', 2.5 => 'value2'],
                'expected' => [],
            ],
            'array with boolean keys' => [
                'input' => [true => 'value', false => 'value2'],
                'expected' => [],
            ],
            'array with object keys' => [
                'input' => [(object)['id' => 1] => 'value', (object)['id' => 2] => 'value2'],
                'expected' => [],
            ],
            'array with single null' => [
                'input' => [null],
                'expected' => [],
            ],
            'array with single empty' => [
                'input' => [[]],
                'expected' => [],
            ],
            'array with single string' => [
                'input' => ['test'],
                'expected' => [],
            ],
            'array with single integer' => [
                'input' => [123],
                'expected' => [],
            ],
            'array with single float' => [
                'input' => [123.45],
                'expected' => [],
            ],
            'array with single boolean' => [
                'input' => [true],
                'expected' => [],
            ],
            'array with single object' => [
                'input' => [(object)['key' => 'value']],
                'expected' => [],
            ],
            'array with single null value' => [
                'input' => [null],
                'expected' => [],
            ],
            'array with single empty string' => [
                'input' => [''],
                'expected' => [],
            ],
            'array with single numeric string' => [
                'input' => ['123'],
                'expected' => [],
            ],
            'array with single unicode string' => [
                'input' => ['🎉'],
                'expected' => [],
            ],
            'array with single special string' => [
                'input' => ['<script>'],
                'expected' => [],
            ],
            'array with single whitespace string' => [
                'input' => ['   '],
                'expected' => [],
            ],
            'array with single newline string' => [
                'input' => ['\n'],
                'expected' => [],
            ],
            'array with single tab string' => [
                'input' => ['\t'],
                'expected' => [],
            ],
            'array with single carriage return string' => [
                'input' => ['\r'],
                'expected' => [],
            ],
            'array with single very long string' => [
                'input' => [str_repeat('x', 1000)],
                'expected' => [],
            ],
            'array with single very long path string' => [
                'input' => [str_repeat('path/', 100)],
                'expected' => [],
            ],
            'array with single deep nested array' => [
                'input' => [[[[[[[]]]]]]],
                'expected' => [],
            ],
            'array with single mixed nested array' => [
                'input' => [[[[[]]], [[[]]], [[]]], [[[[[]]]]], [[[[[]]]]]],
                'expected' => [],
            ],
            'array with single associative array' => [
                'input' => ['key1' => 'value1', 'key2' => 'value2'],
                'expected' => [],
            ],
            'array with single numeric array' => [
                'input' => [0 => 'value0', 1 => 'value1', 2 => 'value2'],
                'expected' => [],
            ],
            'array with single mixed array' => [
                'input' => [0 => 'a', 'b' => 'b', 2 => 'c'],
                'expected' => [],
            ],
            'array with single null keys' => [
                'input' => [null => 'value', 0 => 'zero'],
                'expected' => [],
            ],
            'array with single empty associative keys' => [
                'input' => ['', 'empty', 'value'],
                'expected' => [],
            ],
            'array with single unicode keys' => [
                'input' => ['🎉' => 'value', '🚀' => 'value2'],
                'expected' => [],
            ],
            'array with single special char keys' => [
                'input' => ['<script>' => 'value', 'path/' => 'value2'],
                'expected' => [],
            ],
            'array with single mixed unicode keys' => [
                'input' => ['🎉' => 'value', '123' => 'value2', 'test' => 'value3'],
                'expected' => [],
            ],
            'array with single numeric and string keys' => [
                'input' => [123 => 'value', 'string' => 'value2', 456 => 'value3'],
                'expected' => [],
            ],
            'array with single negative numeric keys' => [
                'input' => [-1 => 'value', -2 => 'value2'],
                'expected' => [],
            ],
            'array with single float keys' => [
                'input' => [1.5 => 'value', 2.5 => 'value2'],
                'expected' => [],
            ],
            'array with single boolean keys' => [
                'input' => [true => 'value', false => 'value2'],
                'expected' => [],
            ],
            'array with single object keys' => [
                'input' => [(object)['id' => 1] => 'value', (object)['id' => 2] => 'value2'],
                'expected' => [],
            ],
            'array with single resource keys' => [
                'input' => [fopen('php://temp', 'r') => 'value'],
                'expected' => [],
            ],
            'array with single directory keys' => [
                'input' => [new \DirectoryIterator('.') => 'value'],
                'expected' => [],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderGetPaths
     */
    public function testGetPaths(mixed $input, array $expected): void
    {
        $dataProcessor = GeneralUtility::makeInstance(LazyFolderCollectionToArray::class);
        $result = $dataProcessor->getPaths($input);
        $this->assertEquals($expected, $result);
    }
}
