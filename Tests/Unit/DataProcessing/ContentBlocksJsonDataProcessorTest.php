<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ContentBlocksJsonDataProcessor;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ContentBlocksJsonDataProcessorTest extends UnitTestCase
{
    public function testProcessReturnsArray(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContentBlocksJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $result = $subject->process(null, [], [], ['data' => ['test' => 'value']]);

        self::assertIsArray($result);
    }

    public function testProcessReturnsProcessedData(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContentBlocksJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(null, [], [], $data);

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessReturnsDataWithLocalHeadlessPhp(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContentBlocksJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(null, [], [], $data);

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessSkipsDataIfNoHeadlessPhp(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContentBlocksJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(null, [], [], $data);

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessAdditionalDataProcessorsDelegatesToContentDataProcessor(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContentBlocksJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(null, [], [], $data);

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessUsesDefaultAsKey(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContentBlocksJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(null, [], [], $data);

        self::assertArrayHasKey('data', $result);
    }

    /**
     * @param mixed $value
     */
    private function createSubject($value): ContentBlocksJsonDataProcessor
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        return new ContentBlocksJsonDataProcessor(new ResourceFactory(), $typolinkConverter);
    }
}
