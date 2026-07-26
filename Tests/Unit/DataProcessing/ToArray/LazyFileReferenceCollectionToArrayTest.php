<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFileReferenceCollectionToArray;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyFileReferenceCollectionToArrayTest extends UnitTestCase
{
    public function testConvertsAllFileReferences(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file1 = $this->createMock(File::class);
        $file1->method('getUid')->willReturn(1);
        $file1->method('getPropertyFromStorage')->willReturn(['title' => 'Image 1', 'alternative' => 'Alt 1']);
        $file1->method('getPublicUrl')->willReturn('/public/image1.jpg');

        $file2 = $this->createMock(File::class);
        $file2->method('getUid')->willReturn(2);
        $file2->method('getPropertyFromStorage')->willReturn(['title' => 'Image 2', 'alternative' => 'Alt 2']);
        $file2->method('getPublicUrl')->willReturn('/public/image2.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file1);
        $fileRepository->addFile($file2);

        $subject = new LazyFileReferenceCollectionToArray(
            [$file1, $file2],
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertArrayHasKey('id', $result[0]);
        self::assertSame(1, $result[0]['id']);
        self::assertArrayHasKey('id', $result[1]);
        self::assertSame(2, $result[1]['id']);
    }

    public function testEachFileBecomesImageArray(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(1);
        $file->method('getPropertyFromStorage')->willReturn(['title' => 'Test Image', 'alternative' => 'Alt Text']);
        $file->method('getPublicUrl')->willReturn('/public/image.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file);

        $subject = new LazyFileReferenceCollectionToArray(
            [$file],
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('id', $result[0]);
        self::assertArrayHasKey('alt', $result[0]);
        self::assertArrayHasKey('title', $result[0]);
        self::assertArrayHasKey('publicUrl', $result[0]);
    }

    public function testHandlesNullFileCollection(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $fileRepository = new FileRepository();

        $subject = new LazyFileReferenceCollectionToArray(
            null,
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesEmptyFileCollection(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $fileRepository = new FileRepository();

        $subject = new LazyFileReferenceCollectionToArray(
            [],
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesNonArrayFileCollection(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $fileRepository = new FileRepository();

        $subject = new LazyFileReferenceCollectionToArray(
            'not_an_array',
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesNullFileInCollection(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(1);
        $file->method('getPropertyFromStorage')->willReturn(['title' => 'Image 1', 'alternative' => 'Alt 1']);
        $file->method('getPublicUrl')->willReturn('/public/image1.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file);

        $subject = new LazyFileReferenceCollectionToArray(
            [$file, null],
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }

    public function testHandlesNonFileObjectInCollection(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(1);
        $file->method('getPropertyFromStorage')->willReturn(['title' => 'Image 1', 'alternative' => 'Alt 1']);
        $file->method('getPublicUrl')->willReturn('/public/image1.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file);

        $subject = new LazyFileReferenceCollectionToArray(
            [$file, 'not_a_file'],
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(1, $result);
    }

    public function testHandlesMultipleFilesWithCrops(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file1 = $this->createMock(File::class);
        $file1->method('getUid')->willReturn(1);
        $file1->method('getPropertyFromStorage')->willReturn([
            'title' => 'Image 1',
            'alternative' => 'Alt 1',
            'crop' => 1,
            'cropHeight' => 500,
            'cropWidth' => 500,
            'cropTop' => 100,
            'cropLeft' => 100,
        ]);
        $file1->method('getPublicUrl')->willReturn('/public/cropped-image1.jpg');

        $file2 = $this->createMock(File::class);
        $file2->method('getUid')->willReturn(2);
        $file2->method('getPropertyFromStorage')->willReturn([
            'title' => 'Image 2',
            'alternative' => 'Alt 2',
            'crop' => 1,
            'cropHeight' => 600,
            'cropWidth' => 600,
            'cropTop' => 200,
            'cropLeft' => 200,
        ]);
        $file2->method('getPublicUrl')->willReturn('/public/cropped-image2.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file1);
        $fileRepository->addFile($file2);

        $subject = new LazyFileReferenceCollectionToArray(
            [$file1, $file2],
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertCount(2, $result);
        self::assertTrue($result[0]['crop']);
        self::assertSame(500, $result[0]['cropHeight']);
        self::assertSame(500, $result[0]['cropWidth']);
        self::assertTrue($result[1]['crop']);
        self::assertSame(600, $result[1]['cropHeight']);
        self::assertSame(600, $result[1]['cropWidth']);
    }

    /**
     * @param mixed $value
     */
    private function createSubject($value): LazyFileReferenceCollectionToArray
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        return new LazyFileReferenceCollectionToArray($value, new FileRepository(), $resourceFactory, $typolinkConverter);
    }
}
