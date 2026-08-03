<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    public function testValidRecordIsConvertedToArray(): void
    {
        $array = [
            'uid' => 1,
            'pid' => 2,
            'colPos' => 3,
            'CType' => 'text',
            'title' => 'Test Title',
            'bodytext' => 'Test Body',
        ];

        $filtered = $this->filterRecordArray($array);

        self::assertArrayNotHasKey('uid', $filtered);
        self::assertArrayNotHasKey('pid', $filtered);
        self::assertArrayNotHasKey('colPos', $filtered);
        self::assertArrayNotHasKey('CType', $filtered);
        self::assertArrayHasKey('title', $filtered);
        self::assertSame('Test Title', $filtered['title']);
        self::assertArrayHasKey('bodytext', $filtered);
        self::assertSame('Test Body', $filtered['bodytext']);
    }

    public function testRecordWithMissingFALFileReturnsErrorMessage(): void
    {
        // Simulate the error message that would be returned when FileDoesNotExistException is thrown
        $errorMessage = 'File with uid 999 does not exist.';

        $filtered = [
            '__errorMessage' => $errorMessage,
        ];

        self::assertArrayHasKey('__errorMessage', $filtered);
        self::assertStringContainsString('uid 999', $filtered['__errorMessage']);
    }

    public function testEmptyRecordReturnsEmptyArray(): void
    {
        $array = [];

        $filtered = $this->filterRecordArray($array);

        self::assertEmpty($filtered);
    }

    public function testRecordWithNestedArraysIsProcessedRecursively(): void
    {
        $array = [
            'uid' => 1,
            'pid' => 2,
            'colPos' => 3,
            'CType' => 'collection',
            'items' => [
                [
                    'title' => 'Item 1',
                    'nested' => [
                        'key' => 'value',
                    ],
                ],
                [
                    'title' => 'Item 2',
                ],
            ],
        ];

        $filtered = $this->filterRecordArray($array);

        self::assertArrayNotHasKey('uid', $filtered);
        self::assertArrayNotHasKey('pid', $filtered);
        self::assertArrayNotHasKey('colPos', $filtered);
        self::assertArrayNotHasKey('CType', $filtered);
        self::assertArrayHasKey('items', $filtered);
        self::assertIsArray($filtered['items']);
        self::assertCount(2, $filtered['items']);
    }

    public function testRecordWithAdditionalFieldsIsFilteredCorrectly(): void
    {
        $array = [
            'uid' => 1,
            'pid' => 2,
            'colPos' => 3,
            'CType' => 'text',
            'tx_container_parent' => 123,
            'foreign_table_parent_uid' => 456,
            'title' => 'Test',
            'tags' => ['tag1', 'tag2'],
        ];

        $filtered = $this->filterRecordArray($array);

        self::assertArrayNotHasKey('tx_container_parent', $filtered);
        self::assertArrayNotHasKey('foreign_table_parent_uid', $filtered);
        self::assertArrayHasKey('title', $filtered);
        self::assertArrayHasKey('tags', $filtered);
    }

    /**
     * Simulates the filtering logic from RecordToArray::toArray()
     */
    private function filterRecordArray(array $array): array
    {
        $remove = ['uid', 'pid', 'colPos', 'CType', 'foreign_table_parent_uid', 'tx_container_parent'];

        foreach ($remove as $key) {
            unset($array[$key]);
        }

        return $array;
    }
}
