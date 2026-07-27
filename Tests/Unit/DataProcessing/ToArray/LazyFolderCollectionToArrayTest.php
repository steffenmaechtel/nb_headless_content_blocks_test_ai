<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    public function testBuildsPathFromBasePathAndIdentifier(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => 'fileadmin']);

        $folder = $this->createMock(Folder::class);
        $folder->method('getStorage')->willReturn($storage);
        $folder->method('getIdentifier')->willReturn('images/foo.jpg');

        $collection = $this->createMock(LazyFolderCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([$folder]));

        $subject = new LazyFolderCollectionToArray($collection);

        self::assertSame(
            [0 => '/fileadminimages/foo.jpg'],
            $subject->toArray()
        );
    }
}
