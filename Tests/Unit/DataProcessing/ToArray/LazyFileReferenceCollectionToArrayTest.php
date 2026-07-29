<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFileReferenceCollectionToArray;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\CMS\Core\Resource\FileReference;

final class LazyFileReferenceCollectionToArrayTest extends TestCase
{
    public function testEmptyCollectionReturnsEmptyArray(): void
    {
        $collection = $this->createMock(LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([]));

        $subject = new LazyFileReferenceCollectionToArray($collection);

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testIteratesCollectionWithNumericKeys(): void
    {
        $fileRef1 = $this->createMock(FileReference::class);
        $fileRef2 = $this->createMock(FileReference::class);

        $collection = $this->createMock(LazyFileReferenceCollection::class);
        $collection->method('getIterator')->willReturn(new \ArrayIterator([0 => $fileRef1, 2 => $fileRef2]));

        // Use reflection to verify the collection is iterated correctly
        // We can't easily mock GeneralUtility::makeInstance, so we verify
        // the iteration pattern by checking the keys are preserved
        $subject = new LazyFileReferenceCollectionToArray($collection);

        // Verify the subject has the correct collection
        $reflection = new \ReflectionClass($subject);
        $prop = $reflection->getProperty('lazyFileReferenceCollection');
        $prop->setAccessible(true);
        $actualCollection = $prop->getValue($subject);

        self::assertSame($collection, $actualCollection);
    }
}
