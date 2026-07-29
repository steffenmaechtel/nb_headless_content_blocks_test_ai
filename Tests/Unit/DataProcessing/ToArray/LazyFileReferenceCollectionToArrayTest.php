<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Generator;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFileReferenceCollectionToArray;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFileReferenceCollectionToArrayTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLazyFileReferenceCollectionToArrayWithMultipleFiles(): void
    {
        $fileReference1 = $this->createMock(FileReference::class);
        $fileReference1->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(1);

        $fileReference2 = $this->createMock(FileReference::class);
        $fileReference2->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(2);

        $fileReference3 = $this->createMock(FileReference::class);
        $fileReference3->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(3);

        $imageService = $this->createMock(\TYPO3\CMS\Extbase\Service\ImageService::class);
        $imageService->expects(self::exactly(3))
            ->method('getImageUri')
            ->willReturn('/file1.png', '/file2.png', '/file3.png');

        GeneralUtility::addInstance(\TYPO3\CMS\Extbase\Service\ImageService::class, $imageService);

        $lazyCollection = $this->createLazyFileReferenceCollection([
            'file1' => $fileReference1,
            'file2' => $fileReference2,
            'file3' => $fileReference3,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('file2', $result);
        self::assertArrayHasKey('file3', $result);
    }

    public function testLazyFileReferenceCollectionToArrayWithOneFile(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(1);

        $imageService = $this->createMock(\TYPO3\CMS\Extbase\Service\ImageService::class);
        $imageService->expects(self::once())
            ->method('getImageUri')
            ->willReturn('/file1.png');

        GeneralUtility::addInstance(\TYPO3\CMS\Extbase\Service\ImageService::class, $imageService);

        $lazyCollection = $this->createLazyFileReferenceCollection([
            'singleFile' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertArrayHasKey('singleFile', $result);
    }

    public function testLazyFileReferenceCollectionToArrayWithEmptyCollection(): void
    {
        $lazyCollection = $this->createLazyFileReferenceCollection([]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testLazyFileReferenceCollectionToArrayEmptyGenerator(): void
    {
        $lazyCollection = $this->createLazyFileReferenceCollection((function (): Generator {
            yield from [];
        })());

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testLazyFileReferenceCollectionToArrayWithIndexedKeys(): void
    {
        $fileReference1 = $this->createMock(FileReference::class);
        $fileReference1->expects(self::once())
            ->method('getUid')
            ->willReturn(1);

        $fileReference2 = $this->createMock(FileReference::class);
        $fileReference2->expects(self::once())
            ->method('getUid')
            ->willReturn(2);

        $imageService = $this->createMock(\TYPO3\CMS\Extbase\Service\ImageService::class);
        $imageService->expects(self::exactly(2))
            ->method('getImageUri')
            ->willReturn('/file1.png', '/file2.png');

        GeneralUtility::addInstance(\TYPO3\CMS\Extbase\Service\ImageService::class, $imageService);

        $lazyCollection = $this->createLazyFileReferenceCollection();
        foreach ($lazyCollection as $key => $value) {
            // This should never execute
        }

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    private function createLazyFileReferenceCollection(array $items = []): LazyFileReferenceCollection
    {
        return new class ($items) extends LazyFileReferenceCollection {
            public function __construct(private readonly array $items) {}

            public function getIterator(): Generator
            {
                foreach ($this->items as $key => $fileReference) {
                    yield $key => $fileReference;
                }
            }
        };
    }
}