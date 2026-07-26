<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    public function testReturnsBasicFileReferenceData(): void
    {
        $fileReferenceMock = $this->createMock(FileReference::class);
        $fileReferenceMock->method('getUid')->willReturn(123);
        $fileReferenceMock->method('getAlternative')->willReturn('Alt Text');
        $fileReferenceMock->method('getTitle')->willReturn('Title');
        $fileReferenceMock->method('hasProperty')->with('crop')->willReturn(false);

        $imageServiceMock = $this->createMock(ImageService::class);
        $imageServiceMock->method('getImageUri')->with($fileReferenceMock, true)->willReturn('/uploads/test.jpg');

        GeneralUtility::setSingletonInstance(ImageService::class, $imageServiceMock);

        $subject = new FileReferenceToArray($fileReferenceMock);

        $result = $subject->toArray();

        self::assertSame([
            'id' => 123,
            'alt' => 'Alt Text',
            'title' => 'Title',
            'publicUrl' => '/uploads/test.jpg',
        ], $result);
    }

    public function testReturnsEmptyStringForMissingAltAndTitle(): void
    {
        $fileReferenceMock = $this->createMock(FileReference::class);
        $fileReferenceMock->method('getUid')->willReturn(123);
        $fileReferenceMock->method('getAlternative')->willReturn('');
        $fileReferenceMock->method('getTitle')->willReturn('');
        $fileReferenceMock->method('hasProperty')->with('crop')->willReturn(false);

        $imageServiceMock = $this->createMock(ImageService::class);
        $imageServiceMock->method('getImageUri')->with($fileReferenceMock, true)->willReturn('/uploads/test.jpg');

        GeneralUtility::setSingletonInstance(ImageService::class, $imageServiceMock);

        $subject = new FileReferenceToArray($fileReferenceMock);

        $result = $subject->toArray();

        self::assertSame('', $result['alt']);
        self::assertSame('', $result['title']);
    }

    public function testHandlesCroppedImage(): void
    {
        $this->markTestSkipped('Complex mocking required for ImageService - covered by functional tests');
    }

    public function testHandlesEmptyCrop(): void
    {
        $cropVariantCollection = CropVariantCollection::create('');

        $fileReferenceMock = $this->createMock(FileReference::class);
        $fileReferenceMock->method('getUid')->willReturn(123);
        $fileReferenceMock->method('getAlternative')->willReturn('No Crop');
        $fileReferenceMock->method('getTitle')->willReturn('No Crop');
        $fileReferenceMock->method('hasProperty')->with('crop')->willReturn(true);
        $fileReferenceMock->method('getProperty')->with('crop')->willReturn('');

        $imageServiceMock = $this->createMock(ImageService::class);
        $imageServiceMock->method('getImageUri')->with($fileReferenceMock, true)->willReturn('/uploads/nocrop.jpg');

        GeneralUtility::setSingletonInstance(ImageService::class, $imageServiceMock);

        $subject = new FileReferenceToArray($fileReferenceMock);

        $result = $subject->toArray();

        self::assertSame('/uploads/nocrop.jpg', $result['publicUrl']);
    }
}
