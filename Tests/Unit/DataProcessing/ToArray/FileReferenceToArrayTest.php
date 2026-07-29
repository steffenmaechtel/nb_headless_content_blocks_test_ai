<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropArea;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileFactory;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testFileReferenceToArrayReturnsBasicProperties(): void
    {
        $fileMock = $this->createMock(File::class);
        $fileMock->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(42);

        $fileMock->expects(self::atLeastOnce())
            ->method('getAlternative')
            ->willReturn('Alt Text');

        $fileMock->expects(self::atLeastOnce())
            ->method('getTitle')
            ->willReturn('Title');

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->expects(self::atLeastOnce())
            ->method('getOriginalResource')
            ->willReturn($fileMock);

        $fileReference->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(42);

        $subject = new FileReferenceToArray($fileReference);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEquals(42, $result['id']);
        self::assertEquals('Alt Text', $result['alt']);
        self::assertEquals('Title', $result['title']);
    }

    public function testFileReferenceToArrayWithCropReturnsProcessedUrl(): void
    {
        $cropVariantCollection = $this->createMock(CropVariantCollection::class);
        $cropArea = $this->createMock(CropArea::class);
        $cropArea->expects(self::once())
            ->method('isEmpty')
            ->willReturn(false);
        $cropArea->expects(self::once())
            ->method('makeAbsoluteBasedOnFile')
            ->willReturn(['crop' => ['x' => 0, 'y' => 0, 'width' => 100, 'height' => 100]]);

        $cropVariantCollection->expects(self::once())
            ->method('getCropArea')
            ->with('default')
            ->willReturn($cropArea);

        $fileMock = $this->createMock(File::class);
        $fileMock->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(42);

        $imageService = $this->createMock(ImageService::class);
        $processedImage = $this->createMock(File::class);
        $imageService->expects(self::once())
            ->method('applyProcessingInstructions')
            ->willReturn($processedImage);

        $imageService->expects(self::once())
            ->method('getImageUri')
            ->with($processedImage, true)
            ->willReturn('/file_processed.png');

        GeneralUtility::addInstance(ImageService::class, $imageService);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->expects(self::atLeastOnce())
            ->method('getOriginalResource')
            ->willReturn($fileMock);

        $fileReference->expects(self::once())
            ->method('getProperty')
            ->with('crop')
            ->willReturn('{"cropAreas":{"default":{"cropArea":{"x":0,"y":0,"width":100,"height":100},"selectedArea":{"x":0,"y":0,"width":100,"height":100}}}}');

        $fileReference->expects(self::once())
            ->method('getUid')
            ->willReturn(42);

        $subject = new FileReferenceToArray($fileReference);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertEquals('/file_processed.png', $result['publicUrl']);
    }

    public function testFileReferenceToArrayWithoutCropReturnsOriginalUrl(): void
    {
        $cropVariantCollection = $this->createMock(CropVariantCollection::class);
        $cropArea = $this->createMock(CropArea::class);
        $cropArea->expects(self::once())
            ->method('isEmpty')
            ->willReturn(true);

        $cropVariantCollection->expects(self::once())
            ->method('getCropArea')
            ->with('default')
            ->willReturn($cropArea);

        $fileMock = $this->createMock(File::class);
        $fileMock->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(42);

        $imageService = $this->createMock(ImageService::class);
        $imageService->expects(self::once())
            ->method('applyProcessingInstructions')
            ->willReturnSelf();

        $imageService->expects(self::once())
            ->method('getImageUri')
            ->with($fileMock, true)
            ->willReturn('/file_original.png');

        GeneralUtility::addInstance(ImageService::class, $imageService);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->expects(self::atLeastOnce())
            ->method('getOriginalResource')
            ->willReturn($fileMock);

        $fileReference->expects(self::once())
            ->method('getProperty')
            ->with('crop')
            ->willReturn(null);

        $fileReference->expects(self::once())
            ->method('getUid')
            ->willReturn(42);

        $subject = new FileReferenceToArray($fileReference);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertEquals('/file_original.png', $result['publicUrl']);
    }

    public function testFileReferenceToArrayWithEmptyCropString(): void
    {
        $cropVariantCollection = $this->createMock(CropVariantCollection::class);
        $cropArea = $this->createMock(CropArea::class);
        $cropArea->expects(self::once())
            ->method('isEmpty')
            ->willReturn(true);

        $cropVariantCollection->expects(self::once())
            ->method('getCropArea')
            ->with('default')
            ->willReturn($cropArea);

        $fileMock = $this->createMock(File::class);
        $fileMock->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(42);

        $imageService = $this->createMock(ImageService::class);
        $imageService->expects(self::once())
            ->method('getImageUri')
            ->with($fileMock, true)
            ->willReturn('/file_original.png');

        GeneralUtility::addInstance(ImageService::class, $imageService);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->expects(self::atLeastOnce())
            ->method('getOriginalResource')
            ->willReturn($fileMock);

        $fileReference->expects(self::once())
            ->method('getProperty')
            ->with('crop')
            ->willReturn('');

        $fileReference->expects(self::once())
            ->method('getUid')
            ->willReturn(42);

        $subject = new FileReferenceToArray($fileReference);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertEquals('/file_original.png', $result['publicUrl']);
    }

    public function testFileReferenceToArrayWithNullCropProperty(): void
    {
        $cropVariantCollection = $this->createMock(CropVariantCollection::class);
        $cropArea = $this->createMock(CropArea::class);
        $cropArea->expects(self::once())
            ->method('isEmpty')
            ->willReturn(true);

        $cropVariantCollection->expects(self::once())
            ->method('getCropArea')
            ->with('default')
            ->willReturn($cropArea);

        $fileMock = $this->createMock(File::class);
        $fileMock->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(42);

        $imageService = $this->createMock(ImageService::class);
        $imageService->expects(self::once())
            ->method('getImageUri')
            ->with($fileMock, true)
            ->willReturn('/file_original.png');

        GeneralUtility::addInstance(ImageService::class, $imageService);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->expects(self::atLeastOnce())
            ->method('getOriginalResource')
            ->willReturn($fileMock);

        $fileReference->expects(self::once())
            ->method('getProperty')
            ->with('crop')
            ->willReturn(null);

        $fileReference->expects(self::once())
            ->method('hasProperty')
            ->with('crop')
            ->willReturn(false);

        $fileReference->expects(self::once())
            ->method('getUid')
            ->willReturn(42);

        $subject = new FileReferenceToArray($fileReference);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertEquals('/file_original.png', $result['publicUrl']);
    }

    public function testFileReferenceToArrayNoHasProperty(): void
    {
        $cropVariantCollection = $this->createMock(CropVariantCollection::class);
        $cropArea = $this->createMock(CropArea::class);
        $cropArea->expects(self::once())
            ->method('isEmpty')
            ->willReturn(true);

        $cropVariantCollection->expects(self::once())
            ->method('getCropArea')
            ->with('default')
            ->willReturn($cropArea);

        $fileMock = $this->createMock(File::class);
        $fileMock->expects(self::atLeastOnce())
            ->method('getUid')
            ->willReturn(42);

        $imageService = $this->createMock(ImageService::class);
        $imageService->expects(self::once())
            ->method('getImageUri')
            ->with($fileMock, true)
            ->willReturn('/file_original.png');

        GeneralUtility::addInstance(ImageService::class, $imageService);

        $fileReference = $this->createMock(FileReference::class);
        $fileReference->expects(self::atLeastOnce())
            ->method('getOriginalResource')
            ->willReturn($fileMock);

        $fileReference->expects(self::once())
            ->method('hasProperty')
            ->with('crop')
            ->willReturn(false);

        $fileReference->expects(self::once())
            ->method('getUid')
            ->willReturn(42);

        $subject = new FileReferenceToArray($fileReference);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertEquals('/file_original.png', $result['publicUrl']);
    }
}