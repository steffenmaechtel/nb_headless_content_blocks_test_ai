<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\Storage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFolderCollectionToArrayTest extends UnitTestCase
{
    private MockObject&LazyFolderCollection $lazyFolderCollection;
    private MockObject&Folder $folder;
    private MockObject&Storage $storage;

    protected function setUp(): void
    {
        $this->lazyFolderCollection = $this->createMock(LazyFolderCollection::class);
        $this->folder = $this->createMock(Folder::class);
        $this->storage = $this->createMock(Storage::class);
    }

    public function testToArrayWithFolders(): void
    {
        $folders = ['key1' => $this->folder];
        $expectedResult = [
            'key1' => '/path/to/folder'
        ];
        
        $this->lazyFolderCollection->method('getIterator')->willReturn(new \ArrayIterator($folders));
        $this->folder->method('getStorage')->willReturn($this->storage);
        $this->storage->method('getConfiguration')->willReturn(['basePath' => 'path/to']);
        $this->folder->method('getIdentifier')->willReturn('/folder');
        
        $subject = new LazyFolderCollectionToArray($this->lazyFolderCollection);
        
        $result = $subject->toArray();
        
        $this->assertEquals($expectedResult, $result);
    }

    public function testToArrayWithEmptyCollection(): void
    {
        $folders = [];
        
        $this->lazyFolderCollection->method('getIterator')->willReturn(new \ArrayIterator($folders));
        
        $subject = new LazyFolderCollectionToArray($this->lazyFolderCollection);
        
        $result = $subject->toArray();
        
        $this->assertEquals([], $result);
    }
}