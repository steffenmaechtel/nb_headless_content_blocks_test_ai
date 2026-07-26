<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsAllFolders(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $folders = [
            '/path/to/folder1',
            '/path/to/folder2',
            '/path/to/folder3',
        ];

        $subject = new LazyFolderCollectionToArray(
            $folders,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertSame('/path/to/folder1', $result[0]);
        self::assertSame('/path/to/folder2', $result[1]);
        self::assertSame('/path/to/folder3', $result[2]);
    }

    public function testPathFormatIsCorrect(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $folders = ['/basePath/identifier'];

        $subject = new LazyFolderCollectionToArray(
            $folders,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertSame('/basePath/identifier', $result[0]);
        self::assertStringStartsWith('/basePath/', $result[0]);
    }

    public function testHandlesNullFolders(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new LazyFolderCollectionToArray(
            null,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesEmptyFolders(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new LazyFolderCollectionToArray(
            [],
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesNonArrayFolders(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new LazyFolderCollectionToArray(
            'not_an_array',
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesNullFolderInCollection(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $folders = ['/path/to/folder1', null, '/path/to/folder3'];

        $subject = new LazyFolderCollectionToArray(
            $folders,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertCount(2, $result);
        self::assertSame('/path/to/folder1', $result[0]);
        self::assertSame('/path/to/folder3', $result[1]);
    }

    public function testHandlesEmptyStringFolderInCollection(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $folders = ['/path/to/folder1', '', '/path/to/folder3'];

        $subject = new LazyFolderCollectionToArray(
            $folders,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertCount(2, $result);
        self::assertSame('/path/to/folder1', $result[0]);
        self::assertSame('/path/to/folder3', $result[1]);
    }

    public function testHandlesMixedNullAndEmptyFolders(): void
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $folders = [null, '', '/valid/path'];

        $subject = new LazyFolderCollectionToArray(
            $folders,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertCount(1, $result);
        self::assertSame('/valid/path', $result[0]);
    }

    /**
     * @param mixed $value
     */
    private function createSubject($value): LazyFolderCollectionToArray
    {
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        return new LazyFolderCollectionToArray($value, $typolinkConverter);
    }
}
