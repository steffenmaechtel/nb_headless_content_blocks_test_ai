<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    public function testToArrayReturnsCorrectDataWithoutCrop(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(123);
        $fileReference->method('getAlternative')->willReturn('Alt Text');
        $fileReference->method('getTitle')->willReturn('Title Text');
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);

        $imageService = $this->createMock(ImageService::class);
        $imageService->method('getImageUri')->willReturn('https://example.com/image.jpg');

        $subject = new FileReferenceToArray($fileReference, $imageService);

        $result = $subject->toArray();

        self::assertSame([
            'id' => 123,
            'alt' => 'Alt Text',
            'title' => 'Title Text',
            'publicUrl' => 'https://example.com/image.jpg',
        ], $result);
    }

    public function testToArrayHandlesCropCorrectly(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(123);
        $fileReference->method('getAlternative')->willReturn('Alt Text');
        $fileReference->method('getTitle')->willReturn('Title Text');
        $fileReference->method('hasProperty')->with('crop')->willReturn(true);
        $fileReference->method('getProperty')->with('crop')->willReturn('some-crop-string');

        $processedFile = $this->createMock(ProcessedFile::class);
        $imageService = $this->createMock(ImageService::class);
        $imageService->method('applyProcessingInstructions')->willReturn($processedFile);
        $imageService->method('getImageUri')->willReturn('https://example.com/cropped-image.jpg');

        $subject = new FileReferenceToArray($fileReference, $imageService);

        $result = $subject->toArray();

        self::assertSame('https://example.com/cropped-image.jpg', $result['publicUrl']);
    }
}
