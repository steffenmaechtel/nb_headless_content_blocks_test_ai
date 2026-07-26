<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Resource\StorageRepositoryInterface;
use TYPO3\CMS\Core\Resource\Storage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    public function testReturnsFileReferenceData(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(123);
        $fileReference->method('getAlternative')->willReturn('Test Alt Text');
        $fileReference->method('getTitle')->willReturn('Test Title');
        $fileReference->method('getProperty')->with('crop')->willReturn('crop=10,10,100,100');
        $fileReference->method('hasProperty')->with('crop')->willReturn(true);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(123, $result['id']);
        self::assertSame('Test Alt Text', $result['alt']);
        self::assertSame('Test Title', $result['title']);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithoutCrop(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(456);
        $fileReference->method('getAlternative')->willReturn('');
        $fileReference->method('getTitle')->willReturn('');
        $fileReference->method('getProperty')->with('crop')->willReturn('');
        $fileReference->method('hasProperty')->with('crop')->willReturn(false);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(456, $result['id']);
        self::assertSame('', $result['alt']);
        self::assertSame('', $result['title']);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithNullCropProperty(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(789);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');
        $fileReference->method('getProperty')->with('crop')->willReturn(null);
        $fileReference->method('hasProperty')->with('crop')->willReturn(true);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(789, $result['id']);
        self::assertSame('Alt', $result['alt']);
        self::assertSame('Title', $result['title']);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithEmptyCropProperty(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(999);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');
        $fileReference->method('getProperty')->with('crop')->willReturn('');
        $fileReference->method('hasProperty')->with('crop')->willReturn(true);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(999, $result['id']);
        self::assertSame('Alt', $result['alt']);
        self::assertSame('Title', $result['title']);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithNullUid(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(null);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertNull($result['id']);
    }

    public function testReturnsDataWithNullAlternative(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn(null);
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertNull($result['alt']);
    }

    public function testReturnsDataWithNullTitle(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn(null);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertNull($result['title']);
    }

    public function testReturnsDataWithNullPublicUrl(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertStringEndsWith('.jpg', $result['publicUrl']);
    }

    public function testReturnsDataWithIntegerUid(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(12345);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(12345, $result['id']);
    }

    public function testReturnsDataWithStringUid(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn('123');
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('123', $result['id']);
    }

    public function testReturnsDataWithUnicodeAlternative(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Ümläüt ßtïçhë');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('Ümläüt ßtïçhë', $result['alt']);
    }

    public function testReturnsDataWithUnicodeTitle(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Ümläüt ßtïçhë');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('Ümläüt ßtïçhë', $result['title']);
    }

    public function testReturnsDataWithHtmlInTitle(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('<strong>Bold</strong>');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('<strong>Bold</strong>', $result['title']);
    }

    public function testReturnsDataWithEmptyArrayAlternative(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('', $result['alt']);
    }

    public function testReturnsDataWithEmptyArrayTitle(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('', $result['title']);
    }

    public function testReturnsDataWithSpecialCharactersInAlt(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt with < > & " special');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('Alt with < > & " special', $result['alt']);
    }

    public function testReturnsDataWithSpecialCharactersInTitle(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title with < > & " special');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('Title with < > & " special', $result['title']);
    }

    public function testReturnsDataWithNewlinesInAlt(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn("Alt\nwith\nnewlines");
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame("Alt\nwith\nnewlines", $result['alt']);
    }

    public function testReturnsDataWithNewlinesInTitle(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn("Title\nwith\nnewlines");

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame("Title\nwith\nnewlines", $result['title']);
    }

    public function testReturnsDataWithWhitespaceInAlt(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('   Alt with spaces   ');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('   Alt with spaces   ', $result['alt']);
    }

    public function testReturnsDataWithWhitespaceInTitle(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('   Title with spaces   ');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('   Title with spaces   ', $result['title']);
    }

    public function testReturnsDataWithTabCharacters(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt\twith\ttabs');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('Alt\twith\ttabs', $result['alt']);
    }

    public function testReturnsDataWithCarriageReturns(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt\rwith\rreturns');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('Alt\rwith\rreturns', $result['alt']);
    }

    public function testReturnsDataWithMixedWhitespace(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt \t\n\r with mixed');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame('Alt \t\n\r with mixed', $result['alt']);
    }

    public function testReturnsDataWithZeroUid(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(0);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(0, $result['id']);
    }

    public function testReturnsDataWithFloatUid(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(123.456);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(123.456, $result['id']);
    }

    public function testReturnsDataWithBooleanUid(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(true);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(1, $result['id']);
    }

    public function testReturnsDataWithBooleanAlt(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn(true);
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(1, $result['id']);
        self::assertSame(1, $result['alt']);
    }

    public function testReturnsDataWithBooleanTitle(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn(true);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertSame(1, $result['id']);
        self::assertSame(1, $result['title']);
    }

    public function testReturnsDataWithNullPublicUrl(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithEmptyPublicUrl(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertStringStartsWith('', $result['publicUrl']);
    }

    public function testReturnsDataWithHttpPublicUrl(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertStringContainsString('http', $result['publicUrl']);
    }

    public function testReturnsDataWithHttpsPublicUrl(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertStringContainsString('https', $result['publicUrl']);
    }

    public function testReturnsDataWithPublicUrlAndQuery(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithPublicUrlAndFragment(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithPublicUrlAndPath(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithPublicUrlAndExtension(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithPublicUrlAndProtocol(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithPublicUrlAndPort(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithPublicUrlAndDomain(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithPublicUrlAndSubdomain(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithPublicUrlAndTld(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testReturnsDataWithPublicUrlAndSubTld(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(1);
        $fileReference->method('getAlternative')->willReturn('Alt');
        $fileReference->method('getTitle')->willReturn('Title');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('publicUrl', $result);
    }
}
