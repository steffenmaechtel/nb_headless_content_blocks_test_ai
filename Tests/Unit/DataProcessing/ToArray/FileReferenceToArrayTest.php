<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;

final class FileReferenceToArrayTest extends TestCase
{
    private ?ImageService $imageService = null;

    protected function tearDown(): void
    {
        if ($this->imageService !== null) {
            GeneralUtility::removeSingletonInstance(ImageService::class, $this->imageService);
        }

        parent::tearDown();
    }

    public function testFileReferenceWithoutCropIsConverted(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->expects($this->once())->method('getUid')->willReturn(42);
        $fileReference->expects($this->once())->method('getAlternative')->willReturn('Alternative');
        $fileReference->expects($this->once())->method('getTitle')->willReturn('Title');

        $imageService = new class ($this->createMock(ResourceFactory::class), $fileReference) extends ImageService implements SingletonInterface {
            public function __construct(
                ResourceFactory $resourceFactory,
                private readonly FileReference $expectedFileReference
            ) {
                parent::__construct($resourceFactory);
            }

            public function getImageUri(FileInterface $image, bool $absolute = false): string
            {
                TestCase::assertSame($this->expectedFileReference, $image);
                TestCase::assertTrue($absolute);

                return 'https://example.com/image.jpg';
            }
        };
        $this->imageService = $imageService;
        GeneralUtility::setSingletonInstance(ImageService::class, $imageService);

        $subject = new FileReferenceToArray($fileReference);

        self::assertSame([
            'id' => 42,
            'alt' => 'Alternative',
            'title' => 'Title',
            'publicUrl' => 'https://example.com/image.jpg',
        ], $subject->toArray());
    }
}
