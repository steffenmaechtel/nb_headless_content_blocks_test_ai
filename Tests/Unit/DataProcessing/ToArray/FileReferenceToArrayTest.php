<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Extbase\Service\ImageService;

final class FileReferenceToArrayTest extends TestCase
{
    public function testReturnsIdAltTitleAndPublicUrl(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getUid')->willReturn(42);
        $fileReference->method('getAlternative')->willReturn('Alt text');
        $fileReference->method('getTitle')->willReturn('My Image');

        $imageService = $this->createMock(ImageService::class);
        $imageService->method('getImageUri')->willReturn('/fileadmin/image.jpg');

        $subject = new class ($fileReference, $imageService) extends FileReferenceToArray {
            public function __construct(
                FileReference $fileReference,
                private ImageService $imageService
            ) {
                parent::__construct($fileReference);
            }

            protected static function getImageService(): ImageService
            {
                return $this->imageService;
            }
        };

        $result = $subject->toArray();

        self::assertSame([
            'id' => 42,
            'alt' => 'Alt text',
            'title' => 'My Image',
            'publicUrl' => '/fileadmin/image.jpg',
        ], $result);
    }

    public function testHandlesEmptyProperties(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('');
        $fileReference->method('getTitle')->willReturn('');

        $imageService = $this->createMock(ImageService::class);
        $imageService->method('getImageUri')->willReturn('/fileadmin/empty.jpg');

        $subject = new class ($fileReference, $imageService) extends FileReferenceToArray {
            public function __construct(
                FileReference $fileReference,
                private ImageService $imageService
            ) {
                parent::__construct($fileReference);
            }

            protected static function getImageService(): ImageService
            {
                return $this->imageService;
            }
        };

        $result = $subject->toArray();

        self::assertSame([
            'id' => 1,
            'alt' => '',
            'title' => '',
            'publicUrl' => '/fileadmin/empty.jpg',
        ], $result);
    }
}