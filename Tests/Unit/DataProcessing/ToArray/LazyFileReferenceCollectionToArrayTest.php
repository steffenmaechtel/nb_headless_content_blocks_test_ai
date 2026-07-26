<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFileReferenceCollectionToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFileReferenceCollectionToArrayTest extends UnitTestCase
{
    private MockObject&LazyFileReferenceCollection $lazyFileReferenceCollection;
    private MockObject&FileReference $fileReference;
    private MockObject&FileReferenceToArray $fileReferenceToArray;

    protected function setUp(): void
    {
        $this->lazyFileReferenceCollection = $this->createMock(LazyFileReferenceCollection::class);
        $this->fileReference = $this->createMock(FileReference::class);
        $this->fileReferenceToArray = $this->createMock(FileReferenceToArray::class);
    }

    public function testToArrayWithFileReferences(): void
    {
        $fileReferences = ['key1' => $this->fileReference];
        $expectedResult = [
            'key1' => [
                'id' => 123,
                'alt' => 'Alternative text',
                'title' => 'Title',
                'publicUrl' => 'https://example.com/image.jpg'
            ]
        ];
        
        $this->lazyFileReferenceCollection->method('getIterator')->willReturn(new \ArrayIterator($fileReferences));
        $this->fileReferenceToArray->method('toArray')->willReturn($expectedResult['key1']);
        
        GeneralUtility::addInstance(FileReferenceToArray::class, $this->fileReferenceToArray);
        
        $subject = new LazyFileReferenceCollectionToArray($this->lazyFileReferenceCollection);
        
        $result = $subject->toArray();
        
        $this->assertEquals($expectedResult, $result);
    }

    public function testToArrayWithEmptyCollection(): void
    {
        $fileReferences = [];
        
        $this->lazyFileReferenceCollection->method('getIterator')->willReturn(new \ArrayIterator($fileReferences));
        
        $subject = new LazyFileReferenceCollectionToArray($this->lazyFileReferenceCollection);
        
        $result = $subject->toArray();
        
        $this->assertEquals([], $result);
    }
}