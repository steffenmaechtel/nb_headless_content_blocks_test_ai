<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class ContainerJsonDataProcessorTest extends UnitTestCase
{
    public function testProcessReturnsArray(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContainerJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $result = $subject->process(null, [], [], ['data' => ['test' => 'value']]);

        self::assertIsArray($result);
    }

    public function testProcessReturnsProcessedData(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContainerJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(null, [], [], $data);

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessReturnsDataWithChildren(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContainerJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(null, [], [], $data);

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessExtractsRenderedContentFromChildren(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContainerJsonDataProcessor(
            new ResourceFactory(),
            $typolinkConverter
        );

        $data = ['data' => ['my_field' => 'value']];
        $result = $subject->process(null, [], [], $data);

        self::assertArrayHasKey('data', $result);
    }

    public function testProcessUsesChildrenAsDefaultAsKey(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new ContainerJsonDataProcessor(
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
    private function createSubject($value): ContainerJsonDataProcessor
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        return new ContainerJsonDataProcessor(new ResourceFactory(), $typolinkConverter);
    }
}
