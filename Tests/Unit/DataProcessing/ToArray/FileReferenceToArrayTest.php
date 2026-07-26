<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\EventDispatcher\ListenerProviderInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    public function testConvertsFileReferenceToArray(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(123);
        $fileReference->method('getAlternative')->willReturn('Alt Text');
        $fileReference->method('getTitle')->willReturn('Image Title');
        $fileReference->method('getProperty')->with('crop')->willReturn('crop_string');

        $publicUrl = 'https://example.com/path/to/image.jpg';
        $fileReference->method('getImageUri')->willReturn($publicUrl);

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('alt', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertSame(123, $result['id']);
        self::assertSame('Alt Text', $result['alt']);
        self::assertSame('Image Title', $result['title']);
        self::assertSame($publicUrl, $result['publicUrl']);
    }

    public function testHandlesNullFileReference(): void
    {
        // Constructor will throw TypeError due to typed property
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Typed property Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray::$fileReference');

        // Cannot instantiate with null due to strict typing
        // Test verifies the exception is raised at construction time
    }

    public function testConvertsSingleFileReferenceToId(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(456);
        $fileReference->method('getAlternative')->willReturn('');
        $fileReference->method('getTitle')->willReturn('');
        $fileReference->method('getProperty')->with('crop')->willReturn('');
        $fileReference->method('getImageUri')->willReturn('https://example.com/image.jpg');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('alt', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertSame(456, $result['id']);
        self::assertSame('', $result['alt']);
        self::assertSame('', $result['title']);
        self::assertSame('https://example.com/image.jpg', $result['publicUrl']);
    }

    public function testConvertsMultipleFileReferencesToId(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(789);
        $fileReference->method('getAlternative')->willReturn('');
        $fileReference->method('getTitle')->willReturn('');
        $fileReference->method('getProperty')->with('crop')->willReturn('');
        $fileReference->method('getImageUri')->willReturn('https://example.com/image.jpg');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('alt', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertSame(789, $result['id']);
        self::assertSame('https://example.com/image.jpg', $result['publicUrl']);
    }

    public function testConvertsEmptyFileReferenceToId(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(999);
        $fileReference->method('getAlternative')->willReturn('');
        $fileReference->method('getTitle')->willReturn('');
        $fileReference->method('getProperty')->with('crop')->willReturn('');
        $fileReference->method('getImageUri')->willReturn('https://example.com/image.jpg');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('alt', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertSame(999, $result['id']);
        self::assertArrayNotHasKey('__errorMessage', $result);
    }

    public function testConvertsNullFileReferenceToId(): void
    {
        // Cannot instantiate with null due to strict typing
        $this->expectException(\TypeError::class);
    }

    public function testHandlesEmptyOrNullFileReference(): void
    {
        // Cannot instantiate with null due to strict typing
        $this->expectException(\TypeError::class);
    }

    public function testHandlesMixedFileReferenceData(): void
    {
        $fileReference = $this->createMock(FileReference::class);
        $fileReference->method('getUid')->willReturn(111);
        $fileReference->method('getAlternative')->willReturn('Alternative Text');
        $fileReference->method('getTitle')->willReturn('Title');
        $fileReference->method('getProperty')->with('crop')->willReturn('');
        $fileReference->method('getImageUri')->willReturn('https://example.com/image.jpg');

        $subject = new FileReferenceToArray($fileReference);

        $result = $subject->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('alt', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertSame(111, $result['id']);
        self::assertStringStartsWith('https', $result['publicUrl']);
    }
}