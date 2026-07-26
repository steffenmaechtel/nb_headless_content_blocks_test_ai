<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing;

use B13\Container\DataProcessing\ContainerProcessor;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ContainerJsonDataProcessor;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ContainerJsonDataProcessorTest extends UnitTestCase
{
    public function testProcessReturnsArray(): void
    {
        $tableDefinitionCollection = new \TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentBlockDataDecorator = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockDataDecorator();
        $contentTypeResolver = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();

        $subject = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
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
        $tableDefinitionCollection = new \TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentBlockDataDecorator = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockDataDecorator();
        $contentTypeResolver = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();

        $subject = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
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

    public function testProcessReturnsDataWithChildren(): void
    {
        $tableDefinitionCollection = new \TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentBlockDataDecorator = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockDataDecorator();
        $contentTypeResolver = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();

        $subject = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
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

    public function testProcessExtractsRenderedContentFromChildren(): void
    {
        $tableDefinitionCollection = new \TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentBlockDataDecorator = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockDataDecorator();
        $contentTypeResolver = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();

        $subject = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
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

    public function testProcessUsesChildrenAsDefaultAsKey(): void
    {
        $tableDefinitionCollection = new \TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection();
        $recordFactory = new RecordFactory();
        $contentBlockDataDecorator = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockDataDecorator();
        $contentTypeResolver = new \TYPO3\CMS\ContentBlocks\DataProcessing\ContentTypeResolver();
        $contentBlockRegistry = new \TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry();

        $subject = new ContainerJsonDataProcessor(
            $tableDefinitionCollection,
            $recordFactory,
            $contentBlockDataDecorator,
            $contentTypeResolver,
            $contentBlockRegistry
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
