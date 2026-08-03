<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinition;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    public function testValidRecordIsConvertedToArray(): void
    {
        $record = new class () extends Record {
            public function toArray(): array
            {
                return [
                    'uid' => 1,
                    'pid' => 2,
                    'colPos' => 3,
                    'CType' => 'text',
                    'title' => 'Test Title',
                    'bodytext' => 'Test Body',
                ];
            }
        };

        $tableDefinition = new TableDefinition('tx_nb_headless_content_blocks_contentblocks');
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $tableDefinitionCollection->addTable($tableDefinition);

        $subject = new RecordToArray(
            $record,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher()
        );

        $result = $subject->toArray();

        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayNotHasKey('colPos', $result);
        self::assertArrayNotHasKey('CType', $result);
        self::assertArrayHasKey('title', $result);
        self::assertSame('Test Title', $result['title']);
        self::assertArrayHasKey('bodytext', $result);
        self::assertSame('Test Body', $result['bodytext']);
    }

    public function testRecordWithMissingFALFileReturnsErrorMessage(): void
    {
        $record = new class () extends Record {
            public function toArray(): array
            {
                return [
                    'uid' => 1,
                    'pid' => 2,
                    'colPos' => 3,
                    'CType' => 'file',
                    'file' => [
                        'uid' => 999,
                        'deleted' => 1,
                    ],
                ];
            }
        };

        $tableDefinition = new TableDefinition('tx_nb_headless_content_blocks_contentblocks');
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $tableDefinitionCollection->addTable($tableDefinition);

        $subject = new RecordToArray(
            $record,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher()
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('__errorMessage', $result);
        self::assertStringContainsString('File does not exist', $result['__errorMessage']);
    }

    public function testEmptyRecordReturnsEmptyArray(): void
    {
        $record = new class () extends Record {
            public function toArray(): array
            {
                return [];
            }
        };

        $tableDefinition = new TableDefinition('tx_nb_headless_content_blocks_contentblocks');
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $tableDefinitionCollection->addTable($tableDefinition);

        $subject = new RecordToArray(
            $record,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher()
        );

        $result = $subject->toArray();

        self::assertEmpty($result);
    }

    public function testRecordWithNestedArraysIsProcessedRecursively(): void
    {
        $record = new class () extends Record {
            public function toArray(): array
            {
                return [
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
            }
        };

        $tableDefinition = new TableDefinition('tx_nb_headless_content_blocks_contentblocks');
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $tableDefinitionCollection->addTable($tableDefinition);

        $subject = new RecordToArray(
            $record,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher()
        );

        $result = $subject->toArray();

        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayNotHasKey('colPos', $result);
        self::assertArrayNotHasKey('CType', $result);
        self::assertArrayHasKey('items', $result);
        self::assertIsArray($result['items']);
        self::assertCount(2, $result['items']);
    }

    public function testRecordWithAdditionalFieldsIsFilteredCorrectly(): void
    {
        $record = new class () extends Record {
            public function toArray(): array
            {
                return [
                    'uid' => 1,
                    'pid' => 2,
                    'colPos' => 3,
                    'CType' => 'text',
                    'tx_container_parent' => 123,
                    'foreign_table_parent_uid' => 456,
                    'title' => 'Test',
                    'tags' => ['tag1', 'tag2'],
                ];
            }
        };

        $tableDefinition = new TableDefinition('tx_nb_headless_content_blocks_contentblocks');
        $tableDefinitionCollection = new TableDefinitionCollection(new AutomaticLanguageKeysRegistry());
        $tableDefinitionCollection->addTable($tableDefinition);

        $subject = new RecordToArray(
            $record,
            $tableDefinition,
            $tableDefinitionCollection,
            $this->createEventDispatcher()
        );

        $result = $subject->toArray();

        self::assertArrayNotHasKey('tx_container_parent', $result);
        self::assertArrayNotHasKey('foreign_table_parent_uid', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('tags', $result);
    }

    /**
     * @param callable[] $listeners
     */
    private function createEventDispatcher(array $listeners = []): EventDispatcher
    {
        $listenerProvider = new class ($listeners) implements \Psr\EventDispatcher\ListenerProviderInterface {
            public function __construct(private readonly array $listeners) {}

            public function getListenersForEvent(object $event): iterable
            {
                return $this->listeners;
            }
        };

        return new EventDispatcher($listenerProvider);
    }
}
