<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $subject = new LazyFolderCollectionToArray($this->createCollection([]));

        self::assertSame([], $subject->toArray());
    }

    public function testSingleFolderIsConvertedToPath(): void
    {
        $folder = $this->createFolder('fileadmin/', '/user_upload/');

        $subject = new LazyFolderCollectionToArray($this->createCollection([$folder]));

        self::assertSame([0 => '/fileadmin/user_upload/'], $subject->toArray());
    }

    public function testMultipleFoldersArePreservedInOrder(): void
    {
        $collection = $this->createCollection([
            $this->createFolder('fileadmin/', '/first/'),
            $this->createFolder('fileadmin/', '/second/'),
        ]);

        $subject = new LazyFolderCollectionToArray($collection);

        self::assertSame([
            0 => '/fileadmin/first/',
            1 => '/fileadmin/second/',
        ], $subject->toArray());
    }

    public function testArrayKeysArePreserved(): void
    {
        $collection = $this->createCollection([
            'alpha' => $this->createFolder('fileadmin/', '/alpha/'),
            'bravo' => $this->createFolder('fileadmin/', '/bravo/'),
        ]);

        $subject = new LazyFolderCollectionToArray($collection);

        self::assertSame([
            'alpha' => '/fileadmin/alpha/',
            'bravo' => '/fileadmin/bravo/',
        ], $subject->toArray());
    }

    public function testLeadingSlashOfIdentifierIsNotDuplicated(): void
    {
        $withSlash = new LazyFolderCollectionToArray(
            $this->createCollection([$this->createFolder('fileadmin/', '/user_upload/')])
        );
        $withoutSlash = new LazyFolderCollectionToArray(
            $this->createCollection([$this->createFolder('fileadmin/', 'user_upload/')])
        );

        self::assertSame([0 => '/fileadmin/user_upload/'], $withSlash->toArray());
        self::assertSame([0 => '/fileadmin/user_upload/'], $withoutSlash->toArray());
    }

    /**
     * Documents current behaviour: the base path is concatenated verbatim, so a
     * storage configuration without a trailing slash produces a joined segment.
     */
    public function testBasePathWithoutTrailingSlashIsConcatenatedVerbatim(): void
    {
        $folder = $this->createFolder('fileadmin', '/user_upload/');

        $subject = new LazyFolderCollectionToArray($this->createCollection([$folder]));

        self::assertSame([0 => '/fileadminuser_upload/'], $subject->toArray());
    }

    public function testNestedIdentifierIsAppendedToBasePath(): void
    {
        $folder = $this->createFolder('fileadmin/', '/user_upload/images/2026/');

        $subject = new LazyFolderCollectionToArray($this->createCollection([$folder]));

        self::assertSame([0 => '/fileadmin/user_upload/images/2026/'], $subject->toArray());
    }

    /**
     * A storage driver without a "basePath" configuration key (for example the
     * fallback storage or a remote driver) must not break the conversion.
     */
    public function testMissingBasePathConfigurationFallsBackToIdentifierOnly(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn([]);
        $folder = new Folder($storage, '/user_upload/', 'user_upload');

        $subject = new LazyFolderCollectionToArray($this->createCollection([$folder]));

        self::assertSame([0 => '/user_upload/'], $subject->toArray());
    }

    /**
     * @param array<array-key, Folder> $folders
     */
    private function createCollection(array $folders): LazyFolderCollection
    {
        return new LazyFolderCollection('', static fn(): array => $folders);
    }

    private function createFolder(string $basePath, string $identifier): Folder
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => $basePath]);

        return new Folder($storage, $identifier, basename(rtrim($identifier, '/')));
    }
}
