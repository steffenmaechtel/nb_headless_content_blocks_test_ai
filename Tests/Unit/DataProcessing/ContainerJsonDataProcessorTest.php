<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ContainerJsonDataProcessor;
use TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockDataDecorator;
use TYPO3\CMS\ContentBlocks\DataProcessing\ContentTypeResolver;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;
use Psr\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ContainerJsonDataProcessorTest extends UnitTestCase
{
    public function testReturnsDataWithoutContainerRenderer(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->with('tt_content')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn(null);

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $contentBlockDataDecorator = $this->createMock(ContentBlockDataDecorator::class);
        $contentBlockDataDecorator->method('get')->willReturn(null);

        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $eventDispatcher = new EventDispatcher($listenerProvider);

        $processor = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
        );

        $processedData = ['data' => ['uid' => 123]];
        $result = $processor->process(
            $this->createMock(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            ['as' => 'data'],
            $processedData
        );
        self::assertArrayHasKey('data', $result);
    }

    public function testReturnsDataForNonNestedType(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->with('tt_content')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $contentBlockDataDecorator = $this->createMock(ContentBlockDataDecorator::class);
        $contentBlockDataDecorator->method('get')->willReturn([]);

        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $eventDispatcher = new EventDispatcher($listenerProvider);

        $processor = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
        );

        $processedData = ['data' => ['uid' => 123]];
        $result = $processor->process(
            $this->createMock(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            ['as' => 'data'],
            $processedData
        );
        self::assertArrayHasKey('data', $result);
    }

    public function testReturnsDataForNestedType(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->with('tt_content')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $contentBlockDataDecorator = $this->createMock(ContentBlockDataDecorator::class);
        $contentBlockDataDecorator->method('get')->willReturn([]);

        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $eventDispatcher = new EventDispatcher($listenerProvider);

        $processor = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
        );

        $processedData = ['data' => ['uid' => 123]];
        $result = $processor->process(
            $this->createMock(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            ['as' => 'data'],
            $processedData
        );
        self::assertArrayHasKey('data', $result);
    }

    public function testProcessesWithContainerRenderer(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->with('tt_content')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $contentBlockDataDecorator = $this->createMock(ContentBlockDataDecorator::class);
        $contentBlockDataDecorator->method('get')->willReturn([]);

        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $eventDispatcher = new EventDispatcher($listenerProvider);

        $processor = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
        );

        $configuration = [
            'as' => 'data',
            'dataProcessing.' => [
                '1' => [
                    'processor' => 'B13\Container\DataProcessing\ContainerProcessor',
                    'additionalParams' => [
                        'containerRowUid' => 1,
                    ],
                ],
            ],
        ];

        $result = $processor->process(
            $this->createMock(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            $configuration,
            ['data' => ['uid' => 123]]
        );
        self::assertArrayHasKey('data', $result);
    }

    public function testUsesContainerProcessorForNestedContainer(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->with('tt_content')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $contentBlockDataDecorator = $this->createMock(ContentBlockDataDecorator::class);
        $contentBlockDataDecorator->method('get')->willReturn([]);

        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $eventDispatcher = new EventDispatcher($listenerProvider);

        $processor = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
        );

        $configuration = [
            'as' => 'data',
            'dataProcessing.' => [
                '1' => [
                    'processor' => 'B13\Container\DataProcessing\ContainerProcessor',
                    'additionalParams' => [
                        'containerRowUid' => 1,
                        'subContainerUid' => 0,
                    ],
                ],
            ],
        ];

        $result = $processor->process(
            $this->createMock(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            $configuration,
            ['data' => ['uid' => 123]]
        );
        self::assertArrayHasKey('data', $result);
    }

    public function testHandlesEmptyProcessorConfiguration(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $contentBlockDataDecorator = $this->createMock(ContentBlockDataDecorator::class);
        $contentBlockDataDecorator->method('get')->willReturn([]);

        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $eventDispatcher = new EventDispatcher($listenerProvider);

        $processor = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
        );

        $result = $processor->process(
            $this->createMock(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            [],
            ['data' => ['uid' => 123]]
        );
    }

    public function testHandlesNestedProcessorConfiguration(): void
    {
        $tableDefinitionCollection = $this->createMock(TableDefinitionCollection::class);
        $tableDefinitionCollection->method('hasTable')->willReturn(true);
        $tableDefinitionCollection->method('getTable')->willReturn(['columns' => []]);

        $recordFactory = $this->createMock(\TYPO3\CMS\Core\Domain\RecordFactory::class);
        $recordFactory->method('createResolvedRecordFromDatabaseRow')->willReturn(['uid' => 123]);

        $contentTypeResolver = $this->createMock(ContentTypeResolver::class);
        $contentTypeResolver->method('resolve')->willReturn($this->createMock(\TYPO3\CMS\ContentBlocks\Definition\ContentType\ContentTypeInterface::class));

        $contentBlockRegistry = $this->createMock(ContentBlockRegistry::class);
        $contentBlockRegistry->method('getContentBlockExtPath')->willReturn('ext_key/');

        $contentBlockDataDecorator = $this->createMock(ContentBlockDataDecorator::class);
        $contentBlockDataDecorator->method('get')->willReturn([]);

        $listenerProvider = $this->createMock(ListenerProviderInterface::class);
        $eventDispatcher = new EventDispatcher($listenerProvider);

        $processor = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
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
            $this->createMock(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            $configuration,
            ['data' => ['uid' => 123]]
        );
        self::assertArrayHasKey('data', $result);
    }
}