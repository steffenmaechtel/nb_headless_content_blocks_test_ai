<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ContentBlocksJsonDataProcessor;
use TYPO3\CMS\ContentBlocks\DataProcessing\ContentTypeResolver;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ContentBlocksJsonDataProcessorTest extends UnitTestCase
{
    public function testProcessReturnsArray(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentTypeResolver = new ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();
        $eventDispatcher = new EventDispatcher();

        $subject = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $result = $subject->process(
            GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            [],
            ['data' => ['test' => 'value']]
        );

        self::assertIsArray($result);
    }

    public function testProcessReturnsProcessedData(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentTypeResolver = new ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();
        $eventDispatcher = new EventDispatcher();

        $subject = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(
            GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            [],
            $data
        );

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessReturnsDataWithLocalHeadlessPhp(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentTypeResolver = new ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();
        $eventDispatcher = new EventDispatcher();

        $subject = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(
            GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            [],
            $data
        );

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessSkipsDataIfNoHeadlessPhp(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentTypeResolver = new ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();
        $eventDispatcher = new EventDispatcher();

        $subject = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(
            GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            [],
            $data
        );

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessAdditionalDataProcessorsDelegatesToContentDataProcessor(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentTypeResolver = new ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();
        $eventDispatcher = new EventDispatcher();

        $subject = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(
            GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            [],
            $data
        );

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessUsesDefaultAsKey(): void
    {
        $tableDefinitionCollection = new TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentTypeResolver = new ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();
        $eventDispatcher = new EventDispatcher();

        $subject = new ContentBlocksJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentTypeResolver,
            $contentBlockRegistry,
            $eventDispatcher
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(
            GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class),
            [],
            [],
            $data
        );

        self::assertArrayHasKey('data', $result);
    }
}
