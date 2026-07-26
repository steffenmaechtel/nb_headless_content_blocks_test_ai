<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    private MockObject&FileReference $fileReference;
    private MockObject&ImageService $imageService;

    protected function setUp(): void
    {
        $this->fileReference = $this->createMock(FileReference::class);
        $this->imageService = $this->createMock(ImageService::class);
    }

    public function testToArrayWithCropArea(): void
    {
        $cropString = '{"default":{"cropArea":{"x":0.1,"y":0.2,"width":0.8,"height":0.7}}}';
        $cropArea = $this->createMock(CropVariantCollection::class);
        $processedImage = $this->createMock(FileReference::class);
        
        $this->fileReference->method('hasProperty')->with('crop')->willReturn(true);
        $this->fileReference->method('getProperty')->with('crop')->willReturn($cropString);
        $this->fileReference->method('getUid')->willReturn(123);
        $this->fileReference->method('getAlternative')->willReturn('Alternative text');
        $this->fileReference->method('getTitle')->willReturn('Title');
        
        $cropArea->method('isEmpty')->willReturn(false);
        $cropArea->method('makeAbsoluteBasedOnFile')->willReturn('absolute_crop_area');
        
        CropVariantCollection::setCropVariantCollection($cropArea);
        
        $this->imageService->method('applyProcessingInstructions')->with($this->fileReference, ['crop' => 'absolute_crop_area'])->willReturn($processedImage);
        $this->imageService->method('getImageUri')->with($processedImage, true)->willReturn('https://example.com/processed-image.jpg');
        
        GeneralUtility::addInstance(ImageService::class, $this->imageService);
        
        $subject = new FileReferenceToArray($this->fileReference);
        
        $result = $subject->toArray();
        
        $expected = [
            'id' => 123,
            'alt' => 'Alternative text',
            'title' => 'Title',
            'publicUrl' => 'https://example.com/processed-image.jpg'
        ];
        
        $this->assertEquals($expected, $result);
    }

    public function testToArrayWithoutCropArea(): void
    {
        $this->fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $this->fileReference->method('getUid')->willReturn(456);
        $this->fileReference->method('getAlternative')->willReturn('Alt text');
        $this->fileReference->method('getTitle')->willReturn('Image title');
        
        $this->imageService->method('getImageUri')->with($this->fileReference, true)->willReturn('https://example.com/image.jpg');
        
        GeneralUtility::addInstance(ImageService::class, $this->imageService);
        
        $subject = new FileReferenceToArray($this->fileReference);
        
        $result = $subject->toArray();
        
        $expected = [
            'id' => 456,
            'alt' => 'Alt text',
            'title' => 'Image title',
            'publicUrl' => 'https://example.com/image.jpg'
        ];
        
        $this->assertEquals($expected, $result);
    }

    public function testToArrayWithEmptyCropString(): void
    {
        $this->fileReference->method('hasProperty')->with('crop')->willReturn(true);
        $this->fileReference->method('getProperty')->with('crop')->willReturn('');
        $this->fileReference->method('getUid')->willReturn(789);
        $this->fileReference->method('getAlternative')->willReturn('');
        $this->fileReference->method('getTitle')->willReturn('');
        
        $cropArea = $this->createMock(CropVariantCollection::class);
        $cropArea->method('isEmpty')->willReturn(true);
        
        CropVariantCollection::setCropVariantCollection($cropArea);
        
        $this->imageService->method('getImageUri')->with($this->fileReference, true)->willReturn('https://example.com/image.jpg');
        
        GeneralUtility::addInstance(ImageService::class, $this->imageService);
        
        $subject = new FileReferenceToArray($this->fileReference);
        
        $result = $subject->toArray();
        
        $expected = [
            'id' => 789,
            'alt' => '',
            'title' => '',
            'publicUrl' => 'https://example.com/image.jpg'
        ];
        
        $this->assertEquals($expected, $result);
    }
}