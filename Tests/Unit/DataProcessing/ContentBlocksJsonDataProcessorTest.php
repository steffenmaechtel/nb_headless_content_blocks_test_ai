<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ContentBlocksJsonDataProcessor;
use Netzbewegung\NbHeadlessContentBlocks\Tests\Fixtures\Extensions\test_nb_headless_content_blocks\ContentBlocks\ContentElements\simple;
use TYPO3\CMS\ContentBlocks\DataProcessing\ContentTypeResolver;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ContentBlocksJsonDataProcessorTest extends UnitTestCase
{
    public function testReturnsProcessedDataWhenTableNotFound(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->with('tt_content')->willReturn(false);

        $recordFactory = $this->createMock(RecordFactory::class);
        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $eventDispatcher = new EventDispatcher();

        $processor = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $processedData = ['data' => ['uid' => 123]];
        $result = $processor->process(
            $this->createMock(ContentObjectRenderer::class),
            [],
            ['as' => 'data'],
            $processedData
        );

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
    }

    public function testProcessesDataWhenTableFound(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->with('tt_content')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(RecordFactory::class);
        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $record = ['uid' => 123, 'title' => 'Test'];
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn($record);

        $contentTypeDefinition = $this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class);
        $contentTypeResolver->method('resolve')->willReturn($contentTypeDefinition);

        $eventDispatcher = $this->createMock(EventDispatcher::class);
        $eventDispatcher->method('dispatch')->willReturn(null);

        $processor = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $processedData = ['data' => ['uid' => 123]];
        $result = $processor->process(
            $this->createMock(ContentObjectRenderer::class),
            [],
            ['as' => 'data'],
            $processedData
        );

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
    }

    public function testReturnsResolvedRecordWhenContentTypeIsNotInterface(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->with('tt_content')->willReturn(true);

        $record = ['uid' => 123, 'title' => 'Test'];
        $recordFactory = $this->createMock(RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn($record);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn(null);

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $eventDispatcher = new EventDispatcher();

        $processor = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $processedData = ['data' => ['uid' => 123]];
        $result = $processor->process(
            $this->createMock(ContentObjectRenderer::class),
            [],
            ['as' => 'data'],
            $processedData
        );

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessesWithCustomAsKey(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $eventDispatcher = $this->createMock(EventDispatcher::class);

        $processor = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $result = $processor->process(
            $this->createMock(ContentObjectRenderer::class),
            [],
            ['as' => 'content'],
            ['data' => ['uid' => 123]]
        );

        self::assertArrayHasKey('content', $result);
        self::assertArrayNotHasKey('data', $result);
    }

    public function testProcessesAdditionalDataProcessors(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $eventDispatcher = new EventDispatcher();

        $processor = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $configuration = [
            'as' => 'data',
            'dataProcessing.' => [
                '1' => [
                    'processor' => 'TYPO3\CMS\Frontend\Page\PageUrl',
                    'additionalParams' => [
                        'useSsl' => 1,
                    ],
                ],
            ],
        ];

        $result = $processor->process(
            $this->createMock(ContentObjectRenderer::class),
            [],
            $configuration,
            ['data' => ['uid' => 123]]
        );

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
    }

    public function testHeadlessPhpFileExistsIsExecuted(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $record = ['uid' => 123];
        $recordFactory = $this->createMock(RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn($record);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $eventDispatcher = new EventDispatcher();

        $processor = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $processedData = ['data' => ['uid' => 123]];
        $result = $processor->process(
            $this->createMock(ContentObjectRenderer::class),
            [],
            ['as' => 'data'],
            $processedData
        );

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
    }

    public function testHeadlessPhpFileDoesNotExistIsSkipped(): void
    {
        $this->expectException(\RuntimeException::class);

        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $record = ['uid' => 123];
        $recordFactory = $this->createMock(RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn($record);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $eventDispatcher = new EventDispatcher();

        $processor = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $processedData = ['data' => ['uid' => 123]];
        $processor->process(
            $this->createMock(ContentObjectRenderer::class),
            [],
            ['as' => 'data'],
            $processedData
        );
    }

    public function testHandlesEmptyProcessorConfiguration(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $eventDispatcher = new EventDispatcher();

        $processor = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $result = $processor->process(
            $this->createMock(ContentObjectRenderer::class),
            [],
            [],
            ['data' => ['uid' => 123]]
        );

        self::assertIsArray($result);
    }

    public function testHandlesNestedProcessorConfiguration(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $eventDispatcher = new EventDispatcher();

        $processor = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $configuration = [
            'as' => 'data',
            'dataProcessing.' => [
                '1' => [
                    'processor' => 'TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer',
                    'additionalParams' => ['field' => 'title'],
                ],
                '2' => [
                    'processor' => 'TYPO3\CMS\Frontend\ContentObject\Data\KeySetProcessor',
                    'additionalParams' => ['as' => 'titles'],
                ],
            ],
        ];

        $result = $processor->process(
            $this->createMock(ContentObjectRenderer::class),
            [],
            $configuration,
            ['data' => ['uid' => 123]]
        );

        self::assertIsArray($result);
        self::assertArrayHasKey('data', $result);
    }
}