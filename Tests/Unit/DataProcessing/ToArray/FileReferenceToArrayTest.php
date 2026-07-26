<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    public function testReturnsBasicFileReferenceData(): void
    {
        $fileReferenceMock = $this->createMock(FileReference::class);
        $fileReferenceMock->method('getUid')->willReturn(123);
        $fileReferenceMock->method('getAlternative')->willReturn('Alt Text');
        $fileReferenceMock->method('getTitle')->willReturn('Title');
        $fileReferenceMock->method('hasProperty')->with('crop')->willReturn(false);

        $imageServiceMock = $this->createMock(ImageService::class);
        $imageServiceMock->method('getImageUri')->with($fileReferenceMock, true)->willReturn('/uploads/test.jpg');

        $this->injectClassMock(ImageService::class, $imageServiceMock);

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

        $this->injectClassMock(ImageService::class, $imageServiceMock);

        $subject = new FileReferenceToArray($fileReferenceMock);

        $result = $subject->toArray();

        self::assertSame('', $result['alt']);
        self::assertSame('', $result['title']);
    }

    public function testHandlesCroppedImage(): void
    {
        $cropString = 'xMin:0|yMin:0|xMax:100|yMax:100';
        $cropVariantCollection = CropVariantCollection::create($cropString);

        $fileReferenceMock = $this->createMock(FileReference::class);
        $fileReferenceMock->method('getUid')->willReturn(123);
        $fileReferenceMock->method('getAlternative')->willReturn('Cropped Image');
        $fileReferenceMock->method('getTitle')->willReturn('Cropped');
        $fileReferenceMock->method('hasProperty')->with('crop')->willReturn(true);
        $fileReferenceMock->method('getProperty')->with('crop')->willReturn($cropString);

        $cropArea = $cropVariantCollection->getCropArea('default');
        $processedImageMock = $this->createMock(File::class);

        $imageServiceMock = $this->createMock(ImageService::class);
        $imageServiceMock->method('applyProcessingInstructions')
            ->with($fileReferenceMock, ['crop' => $cropArea])
            ->willReturn($processedImageMock);
        $imageServiceMock->method('getImageUri')
            ->with($processedImageMock, true)
            ->willReturn('/uploads/processed_test.jpg');

        $this->injectClassMock(ImageService::class, $imageServiceMock);

        $subject = new FileReferenceToArray($fileReferenceMock);

        $result = $subject->toArray();

        self::assertSame('/uploads/processed_test.jpg', $result['publicUrl']);
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

        $this->injectClassMock(ImageService::class, $imageServiceMock);

        $subject = new FileReferenceToArray($fileReferenceMock);

        $result = $subject->toArray();

        self::assertSame('/uploads/nocrop.jpg', $result['publicUrl']);
    }

    private function injectClassMock(string $className, object $mock): void
    {
        $GLOBALS['__typo3_test_instance_mock_' . md5($className)] = $mock;
    }
}
