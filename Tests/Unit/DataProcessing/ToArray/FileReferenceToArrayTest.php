<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    public function testReturnsFileDataWithoutCrop(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(7);
        $fileReference->method('getAlternative')->willReturn('alternative');
        $fileReference->method('getTitle')->willReturn('title');
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getProperty')->with('crop')->willReturn('');

        $imageService = $this->createMock(ImageService::class);
        $imageService->method('getImageUri')->willReturn('https://example.com/img.png');
        GeneralUtility::addInstance(ImageService::class, $imageService);

        $subject = new FileReferenceToArray($fileReference);

        self::assertSame(
            [
                'id' => 7,
                'alt' => 'alternative',
                'title' => 'title',
                'publicUrl' => 'https://example.com/img.png',
            ],
            $subject->toArray()
        );
    }

    public function testReturnsFileDataWithCrop(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(8);
        $fileReference->method('getAlternative')->willReturn('alternative');
        $fileReference->method('getTitle')->willReturn('title');
        $fileReference->method('hasProperty')->with('crop')->willReturn(true);
        $fileReference->method('getProperty')->willReturnMap([
            ['crop', '{"default":{"cropArea":{"x":0.1,"y":0.1,"width":0.8,"height":0.8},"selectedRatio":"NaN","focusArea":null}}'],
            ['width', 100],
            ['height', 100],
        ]);

        $imageService = $this->createMock(ImageService::class);
        $processedFile = $this->createMock(ProcessedFile::class);
        $imageService->method('applyProcessingInstructions')->willReturn($processedFile);
        $imageService->method('getImageUri')->with($processedFile)->willReturn('https://example.com/cropped.png');
        GeneralUtility::addInstance(ImageService::class, $imageService);

        $subject = new FileReferenceToArray($fileReference);

        self::assertSame(
            [
                'id' => 8,
                'alt' => 'alternative',
                'title' => 'title',
                'publicUrl' => 'https://example.com/cropped.png',
            ],
            $subject->toArray()
        );
    }
}
