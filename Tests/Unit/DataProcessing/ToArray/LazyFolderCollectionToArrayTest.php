<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsFolderCollectionToLazyArray(): void
    {
        $folders = [
            '/var/www/html',
            '/home/user/public',
            '/data/uploads',
        ];

        $collection = [
            'folders' => $folders,
        ];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('folders', $result);
        self::assertIsArray($result['folders']);
        self::assertCount(3, $result['folders']);
    }

    public function testHandlesEmptyCollection(): void
    {
        $collection = [
            'folders' => [],
        ];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('folders', $result);
        self::assertIsArray($result['folders']);
        self::assertEmpty($result['folders']);
    }

    public function testHandlesNullCollection(): void
    {
        $collection = null;

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('folders', $result);
        self::assertIsArray($result['folders']);
        self::assertEmpty($result['folders']);
    }

    public function testHandlesMissingKeyWithDefault(): void
    {
        $collection = [];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('folders', $result);
        self::assertIsArray($result['folders']);
        self::assertEmpty($result['folders']);
    }

    public function testHandlesAbsolutePaths(): void
    {
        $folders = [
            '/absolute/path',
            '/another/absolute/path',
        ];

        $collection = [
            'folders' => $folders,
        ];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertArrayHasKey('folders', $result);
        self::assertStringStartsWith('/absolute', $result['folders'][0]);
        self::assertStringStartsWith('/another', $result['folders'][1]);
    }

    public function testHandlesRelativePaths(): void
    {
        $folders = [
            'relative/path',
            '../parent/path',
            './current/path',
        ];

        $collection = [
            'folders' => $folders,
        ];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertArrayHasKey('folders', $result);
        self::assertSame('relative/path', $result['folders'][0]);
        self::assertSame('../parent/path', $result['folders'][1]);
        self::assertSame('./current/path', $result['folders'][2]);
    }

    public function testPreservesLazyLoadingStructure(): void
    {
        $folders = ['/path1', '/path2'];

        $collection = [
            'folders' => $folders,
        ];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertArrayHasKey('folders', $result);
        self::assertIsArray($result['folders']);
        self::assertArrayNotHasKey('original', $result);
    }

    public function testHandlesInvalidPaths(): void
    {
        $folders = [
            '',
            null,
            'not a path',
        ];

        $collection = [
            'folders' => $folders,
        ];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertArrayHasKey('folders', $result);
        self::assertIsArray($result['folders']);
    }

    public function testHandlesPathsWithTrailingSlashes(): void
    {
        $folders = [
            '/path/to/folder/',
            '/another/folder/',
        ];

        $collection = [
            'folders' => $folders,
        ];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertStringEndsWith('/', $result['folders'][0]);
        self::assertStringEndsWith('/', $result['folders'][1]);
    }

    public function testHandlesPathsWithMultipleTrailingSlashes(): void
    {
        $folders = [
            '/path/to/folder///',
            '/another///',
        ];

        $collection = [
            'folders' => $folders,
        ];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertStringEndsWith('/', $result['folders'][0]);
        self::assertStringEndsWith('/', $result['folders'][1]);
    }

    public function testHandlesSpecialCharactersInPaths(): void
    {
        $folders = [
            '/path with spaces',
            '/path/with/dots.123',
            '/path-2024.01.01',
        ];

        $collection = [
            'folders' => $folders,
        ];

        $subject = new LazyFolderCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertStringContainsString('spaces', $result['folders'][0]);
        self::assertStringContainsString('dots.123', $result['folders'][1]);
        self::assertStringContainsString('2024.01.01', $result['folders'][2]);
    }
}