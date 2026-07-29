<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing;

use B13\Container\DataProcessing\ContainerProcessor;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ContainerJsonDataProcessor;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockDataDecorator;
use TYPO3\CMS\ContentBlocks\DataProcessing\ContentTypeResolver;
use TYPO3\CMS\ContentBlocks\Definition\TableDefinitionCollection;
use TYPO3\CMS\ContentBlocks\Registry\AutomaticLanguageKeysRegistry;
use TYPO3\CMS\ContentBlocks\Registry\ContentBlockRegistry;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

final class ContainerJsonDataProcessorTest extends TestCase
{
    public function testChildrenIsUsedAsDefaultOutputKey(): void
    {
        $containerProcessor = $this->createMock(ContainerProcessor::class);
        $containerProcessor->expects($this->once())
            ->method('process')
            ->willReturn([
                'children' => [
                    ['renderedContent' => 'first'],
                    ['renderedContent' => 'second'],
                ],
                'other' => 'preserved',
            ]);
        GeneralUtility::addInstance(ContainerProcessor::class, $containerProcessor);

        $contentObjectRenderer = $this->createMock(ContentObjectRenderer::class);
        $contentObjectRenderer->expects($this->once())
            ->method('stdWrapValue')
            ->with('as', [], 'children')
            ->willReturn('children');

        $subject = new ContainerJsonDataProcessor(
            new TableDefinitionCollection(new AutomaticLanguageKeysRegistry()),
            self::createStub(RecordFactory::class),
            (new \ReflectionClass(ContentBlockDataDecorator::class))->newInstanceWithoutConstructor(),
            self::createStub(ContentTypeResolver::class),
            (new \ReflectionClass(ContentBlockRegistry::class))->newInstanceWithoutConstructor()
        );

        self::assertSame([
            'children' => ['first', 'second'],
            'other' => 'preserved',
        ], $subject->process($contentObjectRenderer, [], [], []));
    }
}
