<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFileReferenceCollectionToArray;
use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFileReferenceCollectionToArrayTest extends UnitTestCase
{
    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $collectionMock = $this->createMock(LazyFileReferenceCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([]));

        $subject = new LazyFileReferenceCollectionToArray($collectionMock);

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testConvertsMultipleFileReferences(): void
    {
        $fileReference1Mock = $this->createMock(FileReference::class);
        $fileReference1Mock->method('getUid')->willReturn(1);
        $fileReference1Mock->method('getAlternative')->willReturn('Alt 1');
        $fileReference1Mock->method('getTitle')->willReturn('Title 1');
        $fileReference1Mock->method('hasProperty')->with('crop')->willReturn(false);

        $fileReference2Mock = $this->createMock(FileReference::class);
        $fileReference2Mock->method('getUid')->willReturn(2);
        $fileReference2Mock->method('getAlternative')->willReturn('Alt 2');
        $fileReference2Mock->method('getTitle')->willReturn('Title 2');
        $fileReference2Mock->method('hasProperty')->with('crop')->willReturn(false);

        $collectionMock = $this->createMock(LazyFileReferenceCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([0 => $fileReference1Mock, 1 => $fileReference2Mock]));

        $imageServiceMock = $this->createMock(ImageService::class);
        $imageServiceMock->method('getImageUri')->willReturnCallback(
            static function ($fileReference) {
                return '/uploads/file_' . $fileReference->getUid() . '.jpg';
            }
        );

        $this->injectClassMock(ImageService::class, $imageServiceMock);

        $subject = new LazyFileReferenceCollectionToArray($collectionMock);

        $result = $subject->toArray();

        self::assertCount(2, $result);
        self::assertSame([
            'id' => 1,
            'alt' => 'Alt 1',
            'title' => 'Title 1',
            'publicUrl' => '/uploads/file_1.jpg',
        ], $result[0]);
        self::assertSame([
            'id' => 2,
            'alt' => 'Alt 2',
            'title' => 'Title 2',
            'publicUrl' => '/uploads/file_2.jpg',
        ], $result[1]);
    }

    public function testPreservesNumericKeys(): void
    {
        $fileReferenceMock = $this->createMock(FileReference::class);
        $fileReferenceMock->method('getUid')->willReturn(5);
        $fileReferenceMock->method('getAlternative')->willReturn('');
        $fileReferenceMock->method('getTitle')->willReturn('');
        $fileReferenceMock->method('hasProperty')->with('crop')->willReturn(false);

        $collectionMock = $this->createMock(LazyFileReferenceCollection::class);
        $collectionMock->method('getIterator')->willReturn(new \ArrayIterator([42 => $fileReferenceMock]));

        $imageServiceMock = $this->createMock(ImageService::class);
        $imageServiceMock->method('getImageUri')->willReturn('/uploads/test.jpg');

        $this->injectClassMock(ImageService::class, $imageServiceMock);

        $subject = new LazyFileReferenceCollectionToArray($collectionMock);

        $result = $subject->toArray();

        self::assertArrayHasKey(42, $result);
    }

    private function injectClassMock(string $className, object $mock): void
    {
        $GLOBALS['__typo3_test_instance_mock_' . md5($className)] = $mock;
    }
}
