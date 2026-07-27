<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFileReferenceCollectionToArray;
use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFileReferenceCollectionToArrayTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    public function testMapsEachReference(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(11);
        $fileReference->method('getAlternative')->willReturn('alt text');
        $fileReference->method('getTitle')->willReturn('title text');
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);
        $fileReference->method('getProperty')->with('crop')->willReturn('');

        $imageService = $this->createMock(ImageService::class);
        $imageService->method('getImageUri')->willReturn('https://example.com/image.png');
        GeneralUtility::addInstance(ImageService::class, $imageService);

        $collection = $this->createMock(LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([$fileReference]));

        $subject = new LazyFileReferenceCollectionToArray($collection);

        self::assertSame(
            [0 => [
                'id' => 11,
                'alt' => 'alt text',
                'title' => 'title text',
                'publicUrl' => 'https://example.com/image.png',
            ]],
            $subject->toArray()
        );
    }
}
