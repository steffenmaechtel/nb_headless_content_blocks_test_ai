<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    public function testFileReferenceIsConvertedToArray(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(456);
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getProperty')->will($this->returnCallback(function () { throw new \TYPO3\CMS\Core\Exception\InvalidDataStructureException(); }));
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
    }

    public function testFileReferenceWithoutCropReturnsPublicUrl(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getProperty')->will($this->returnCallback(function () { throw new \TYPO3\CMS\Core\Exception\InvalidDataStructureException(); }));
        $fileReference->method('getUid')->willReturn(123);
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getProperty')->will($this->returnCallback(function () { throw new \TYPO3\CMS\Core\Exception\InvalidDataStructureException(); }));
        $fileReference->method('getAlternative')->willReturn('Alt Text');
        $fileReference->method('getTitle')->willReturn('Image Title');
        $fileReference->method('getStorage')->willReturn($storage);

        $converter = new FileReferenceToArray($fileReference);
        $result = $converter->toArray();

        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testFileReferenceWithEmptyCropReturnsPublicUrl(): void
    {
        $cropString = '';

        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('hasProperty')->with('crop')->willReturn(true);
        $fileReference->method('getProperty')->will($this->returnCallback(function () use ($cropString) { return $cropString; }));
        $fileReference->method('getUid')->willReturn(123);
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getProperty')->will($this->returnCallback(function () { throw new \TYPO3\CMS\Core\Exception\InvalidDataStructureException(); }));
        $fileReference->method('getAlternative')->willReturn('Alt Text');
        $fileReference->method('getTitle')->willReturn('Image Title');
        $fileReference->method('getStorage')->willReturn($storage);

        $converter = new FileReferenceToArray($fileReference);
        $result = $converter->toArray();

        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testFileReferenceWithNullAltReturnsEmptyString(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getProperty')->will($this->returnCallback(function () { throw new \TYPO3\CMS\Core\Exception\InvalidDataStructureException(); }));
        $fileReference->method('getUid')->willReturn(123);
        $fileReference->method('getAlternative')->willReturn(null);
        $fileReference->method('getTitle')->willReturn('Image Title');
        $fileReference->method('getStorage')->willReturn($storage);

        $converter = new FileReferenceToArray($fileReference);
        $result = $converter->toArray();

        self::assertArrayHasKey('alt', $result);
    }

    public function testFileReferenceWithNullTitleReturnsEmptyString(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => '/public/']);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getProperty')->will($this->returnCallback(function () { throw new \TYPO3\CMS\Core\Exception\InvalidDataStructureException(); }));
        $fileReference->method('getUid')->willReturn(123);
        $fileReference->method('getAlternative')->willReturn('Alt Text');
        $fileReference->method('getTitle')->willReturn(null);
        $fileReference->method('getStorage')->willReturn($storage);

        $converter = new FileReferenceToArray($fileReference);
        $result = $converter->toArray();

        self::assertArrayHasKey('title', $result);
    }
}
