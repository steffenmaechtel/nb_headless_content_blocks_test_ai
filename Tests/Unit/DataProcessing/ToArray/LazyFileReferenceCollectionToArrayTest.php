<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFileReferenceCollectionToArray;
use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFileReferenceCollectionToArrayTest extends UnitTestCase
{
    public function testReturnsEmptyArrayForEmptyCollection(): void
    {
        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testReturnsArrayWithDataForSingleFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(123);
        $fileReference->method('getAlternative')->willReturn('Alt Text');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('id', $result['file1']);
        self::assertArrayHasKey('alt', $result['file1']);
        self::assertArrayHasKey('title', $result['file1']);
        self::assertArrayHasKey('publicUrl', $result['file1']);
        self::assertSame(123, $result['file1']['id']);
        self::assertSame('Alt Text', $result['file1']['alt']);
        self::assertSame('Title', $result['file1']['title']);
    }

    public function testReturnsArrayWithDataForMultipleFileReferences(): void
    {
        $fileReference1 = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference1->method('getUid')->willReturn(1);
        $fileReference1->method('getAlternative')->willReturn('Alt 1');
        $fileReference1->method('getTitle')->willReturn('Title 1');

        $fileReference2 = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference2->method('getUid')->willReturn(2);
        $fileReference2->method('getAlternative')->willReturn('Alt 2');
        $fileReference2->method('getTitle')->willReturn('Title 2');

        $fileReference3 = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference3->method('getUid')->willReturn(3);
        $fileReference3->method('getAlternative')->willReturn('Alt 3');
        $fileReference3->method('getTitle')->willReturn('Title 3');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference1,
            'file2' => $fileReference2,
            'file3' => $fileReference3,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('file2', $result);
        self::assertArrayHasKey('file3', $result);
        self::assertSame(1, $result['file1']['id']);
        self::assertSame(2, $result['file2']['id']);
        self::assertSame(3, $result['file3']['id']);
    }

    public function testReturnsArrayWithNullDataForNullFileReference(): void
    {
        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => null,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertNull($result['file1']);
    }

    public function testReturnsArrayWithNullUidForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(null);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertNull($result['file1']['id']);
    }

    public function testReturnsArrayWithEmptyAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('', $result['file1']['alt']);
    }

    public function testReturnsArrayWithEmptyTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('', $result['file1']['title']);
    }

    public function testReturnsArrayWithNullPublicUrlForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithNumericKeys(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            0 => $fileReference,
            1 => $fileReference,
            2 => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey(1, $result);
        self::assertArrayHasKey(2, $result);
    }

    public function testReturnsArrayWithStringKeys(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'key1' => $fileReference,
            'key2' => $fileReference,
            'key3' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('key1', $result);
        self::assertArrayHasKey('key2', $result);
        self::assertArrayHasKey('key3', $result);
    }

    public function testReturnsArrayWithMixedKeys(): void
    {
        $fileReference1 = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference1->method('getUid')->willReturn(1);
        $fileReference1->method('getAlternative')->willReturn('Alt 1');
        $fileReference1->method('getTitle')->willReturn('Title 1');

        $fileReference2 = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference2->method('getUid')->willReturn(2);
        $fileReference2->method('getAlternative')->willReturn('Alt 2');
        $fileReference2->method('getTitle')->willReturn('Title 2');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            0 => $fileReference1,
            'key' => $fileReference2,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey(0, $result);
        self::assertArrayHasKey('key', $result);
    }

    public function testReturnsArrayWithUnicodeAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Ümläüt ßtïçhë');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Ümläüt ßtïçhë', $result['file1']['alt']);
    }

    public function testReturnsArrayWithUnicodeTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Ümläüt ßtïçhë');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Ümläüt ßtïçhë', $result['file1']['title']);
    }

    public function testReturnsArrayWithHtmlInAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('<strong>Bold</strong>');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('<strong>Bold</strong>', $result['file1']['alt']);
    }

    public function testReturnsArrayWithHtmlInTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('<strong>Bold</strong>');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('<strong>Bold</strong>', $result['file1']['title']);
    }

    public function testReturnsArrayWithEmptyArrayAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('', $result['file1']['alt']);
    }

    public function testReturnsArrayWithEmptyArrayTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('', $result['file1']['title']);
    }

    public function testReturnsArrayWithSpecialCharactersInAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt with < > & " special');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Alt with < > & " special', $result['file1']['alt']);
    }

    public function testReturnsArrayWithSpecialCharactersInTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title with < > & " special');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Title with < > & " special', $result['file1']['title']);
    }

    public function testReturnsArrayWithNewlinesInAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn("Alt\nwith\nnewlines");
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame("Alt\nwith\nnewlines", $result['file1']['alt']);
    }

    public function testReturnsArrayWithNewlinesInTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn("Title\nwith\nnewlines");

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame("Title\nwith\nnewlines", $result['file1']['title']);
    }

    public function testReturnsArrayWithWhitespaceInAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('   Alt with spaces   ');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('   Alt with spaces   ', $result['file1']['alt']);
    }

    public function testReturnsArrayWithWhitespaceInTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('   Title with spaces   ');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('   Title with spaces   ', $result['file1']['title']);
    }

    public function testReturnsArrayWithTabCharactersInAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt\twith\ttabs');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Alt\twith\ttabs', $result['file1']['alt']);
    }

    public function testReturnsArrayWithTabCharactersInTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title\twith\ttabs');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Title\twith\ttabs', $result['file1']['title']);
    }

    public function testReturnsArrayWithCarriageReturnsInAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt\rwith\rreturns');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Alt\rwith\rreturns', $result['file1']['alt']);
    }

    public function testReturnsArrayWithCarriageReturnsInTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title\rwith\rreturns');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Title\rwith\rreturns', $result['file1']['title']);
    }

    public function testReturnsArrayWithMixedWhitespaceInAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt \t\n\r with mixed');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Alt \t\n\r with mixed', $result['file1']['alt']);
    }

    public function testReturnsArrayWithMixedWhitespaceInTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title \t\n\r with mixed');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame('Title \t\n\r with mixed', $result['file1']['title']);
    }

    public function testReturnsArrayWithZeroUidForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(0);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame(0, $result['file1']['id']);
    }

    public function testReturnsArrayWithFloatUidForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(123.456);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame(123.456, $result['file1']['id']);
    }

    public function testReturnsArrayWithBooleanUidForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(true);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame(1, $result['file1']['id']);
    }

    public function testReturnsArrayWithBooleanAltForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn(true);
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame(1, $result['file1']['id']);
        self::assertSame(1, $result['file1']['alt']);
    }

    public function testReturnsArrayWithBooleanTitleForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn(true);

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertSame(1, $result['file1']['id']);
        self::assertSame(1, $result['file1']['title']);
    }

    public function testReturnsArrayWithNullPublicUrlForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithEmptyPublicUrlForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertStringStartsWith('', $result['file1']['publicUrl']);
    }

    public function testReturnsArrayWithHttpPublicUrlForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertStringContainsString('http', $result['file1']['publicUrl']);
    }

    public function testReturnsArrayWithHttpsPublicUrlForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertStringContainsString('https', $result['file1']['publicUrl']);
    }

    public function testReturnsArrayWithPublicUrlAndQueryForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithPublicUrlAndFragmentForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithPublicUrlAndPathForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithPublicUrlAndExtensionForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithPublicUrlAndProtocolForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithPublicUrlAndPortForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithPublicUrlAndDomainForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithPublicUrlAndSubdomainForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithPublicUrlAndTldForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }

    public function testReturnsArrayWithPublicUrlAndSubTldForFileReference(): void
    {
        $fileReference = $this->createMock(\TYPO3\CMS\Core\Resource\FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $lazyCollection = $this->createMock(LazyFileReferenceCollection::class);
        $lazyCollection->method('__iterative')->willReturn([
            'file1' => $fileReference,
        ]);

        $subject = new LazyFileReferenceCollectionToArray($lazyCollection);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('file1', $result);
        self::assertArrayHasKey('publicUrl', $result['file1']);
    }
}
