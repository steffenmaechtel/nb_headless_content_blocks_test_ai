<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    public function testFileReferenceIsConvertedToArray(): void
    {
        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(123);
        $file->method('getAlternative')->willReturn('Alt Text');
        $file->method('getTitle')->willReturn('Image Title');
        $file->method('getPublicPath')->willReturn('/public/path/to/image.jpg');

        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(456);
        $fileReference->method('getProperty')->with('crop')->willReturn('crop:100,200,300,400');
        $fileReference->method('hasProperty')->with('crop')->willReturn(true);
        $fileReference->method('getAlternative')->willReturn('Alt Text');
        $fileReference->method('getTitle')->willReturn('Image Title');
        $fileReference->method('getStorage')->willReturn($storage);

        $converter = new FileReferenceToArray($fileReference);
        $result = $converter->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertSame(456, $result['id']);
        self::assertArrayHasKey('alt', $result);
        self::assertSame('Alt Text', $result['alt']);
        self::assertArrayHasKey('title', $result);
        self::assertSame('Image Title', $result['title']);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertStringStartsWith('http', $result['publicUrl']);
    }

    public function testFileReferenceWithoutCropReturnsPublicUrl(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(789);
        $fileReference->method('getProperty')->with('crop')->willReturn('');
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getAlternative')->willReturn('No Alt');
        $fileReference->method('getTitle')->willReturn('No Title');
        $fileReference->method('getStorage')->willReturn($storage);

        $converter = new FileReferenceToArray($fileReference);
        $result = $converter->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertSame(789, $result['id']);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertStringStartsWith('http', $result['publicUrl']);
    }

    public function testFileReferenceWithEmptyCropReturnsPublicUrl(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(101);
        $fileReference->method('getProperty')->with('crop')->willReturn('');
        $fileReference->method('hasProperty')->with('crop')->willReturn(true);
        $fileReference->method('getAlternative')->willReturn('Empty Alt');
        $fileReference->method('getTitle')->willReturn('Empty Title');
        $fileReference->method('getStorage')->willReturn($storage);

        $converter = new FileReferenceToArray($fileReference);
        $result = $converter->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertSame(101, $result['id']);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testFileReferenceWithNullAltReturnsEmptyString(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(202);
        $fileReference->method('getAlternative')->willReturn(null);
        $fileReference->method('getTitle')->willReturn('Title Only');
        $fileReference->method('getStorage')->willReturn($storage);

        $converter = new FileReferenceToArray($fileReference);
        $result = $converter->toArray();

        self::assertArrayHasKey('alt', $result);
        self::assertEmpty($result['alt']);
    }

    public function testFileReferenceWithNullTitleReturnsEmptyString(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(303);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn(null);
        $fileReference->method('getStorage')->willReturn($storage);

        $converter = new FileReferenceToArray($fileReference);
        $result = $converter->toArray();

        self::assertArrayHasKey('title', $result);
        self::assertEmpty($result['title']);
    }
}
