<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\FileReferenceToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class FileReferenceToArrayTest extends UnitTestCase
{
    public function testReturnsImageArray(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(1);
        $file->method('getPropertyFromStorage')->willReturn(['title' => 'Test Image', 'alternative' => 'Alt Text']);
        $file->method('getPublicUrl')->willReturn('/public/image.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file);

        $subject = new FileReferenceToArray(
            $file,
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('id', $result);
        self::assertSame(1, $result['id']);
        self::assertArrayHasKey('alt', $result);
        self::assertSame('Alt Text', $result['alt']);
        self::assertArrayHasKey('title', $result);
        self::assertSame('Test Image', $result['title']);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertSame('/public/image.jpg', $result['publicUrl']);
    }

    public function testReturnsEmptyArrayForNullFile(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $fileRepository = new FileRepository();

        $subject = new FileReferenceToArray(
            null,
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testReturnsEmptyArrayForNonFileObject(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $fileRepository = new FileRepository();

        $subject = new FileReferenceToArray(
            'not_a_file',
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testHandlesEmptyFileProperties(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(1);
        $file->method('getPropertyFromStorage')->willReturn([]);
        $file->method('getPublicUrl')->willReturn('/public/image.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file);

        $subject = new FileReferenceToArray(
            $file,
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertArrayNotHasKey('alt', $result);
        self::assertArrayNotHasKey('title', $result);
        self::assertArrayHasKey('publicUrl', $result);
    }

    public function testHandlesCroppedImages(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(1);
        $file->method('getPropertyFromStorage')->willReturn([
            'title' => 'Test Image',
            'alternative' => 'Alt Text',
            'crop' => 1,
            'cropHeight' => 500,
            'cropWidth' => 500,
            'cropTop' => 100,
            'cropLeft' => 100,
        ]);
        $file->method('getPublicUrl')->willReturn('/public/cropped-image.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file);

        $subject = new FileReferenceToArray(
            $file,
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertArrayHasKey('alt', $result);
        self::assertArrayHasKey('title', $result);
        self::assertArrayHasKey('publicUrl', $result);
        self::assertTrue($result['crop']);
        self::assertSame(500, $result['cropHeight']);
        self::assertSame(500, $result['cropWidth']);
        self::assertSame(100, $result['cropTop']);
        self::assertSame(100, $result['cropLeft']);
    }

    public function testHandlesNonCroppedImages(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(1);
        $file->method('getPropertyFromStorage')->willReturn([
            'title' => 'Test Image',
            'alternative' => 'Alt Text',
        ]);
        $file->method('getPublicUrl')->willReturn('/public/image.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file);

        $subject = new FileReferenceToArray(
            $file,
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('id', $result);
        self::assertArrayNotHasKey('crop', $result);
        self::assertArrayNotHasKey('cropHeight', $result);
        self::assertArrayNotHasKey('cropWidth', $result);
        self::assertArrayNotHasKey('cropTop', $result);
        self::assertArrayNotHasKey('cropLeft', $result);
    }

    public function testUsesDefaultCropVariant(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(1);
        $file->method('getPropertyFromStorage')->willReturn([
            'title' => 'Test Image',
            'alternative' => 'Alt Text',
            'crop' => 1,
            'cropHeight' => 500,
            'cropWidth' => 500,
            'cropTop' => 100,
            'cropLeft' => 100,
        ]);
        $file->method('getPublicUrl')->willReturn('/public/image.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file);

        $subject = new FileReferenceToArray(
            $file,
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('crop', $result);
    }

    public function testReturnsPublicUrl(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $file = $this->createMock(File::class);
        $file->method('getUid')->willReturn(1);
        $file->method('getPropertyFromStorage')->willReturn([
            'title' => 'Test Image',
            'alternative' => 'Alt Text',
        ]);
        $file->method('getPublicUrl')->willReturn('/public/image.jpg');

        $fileRepository = new FileRepository();
        $fileRepository->addFile($file);

        $subject = new FileReferenceToArray(
            $file,
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('publicUrl', $result);
        self::assertNotEmpty($result['publicUrl']);
    }

    public function testHandlesMultipleFiles(): void
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

        $subject = new FileReferenceToArray(
            [$file1, $file2],
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(2, $result);
        self::assertSame(1, $result[0]['id']);
        self::assertSame(2, $result[1]['id']);
    }

    public function testReturnsEmptyArrayForEmptyFileCollection(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $fileRepository = new FileRepository();

        $subject = new FileReferenceToArray(
            [],
            $fileRepository,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    /**
     * @param mixed $value
     */
    private function createSubject($value): FileReferenceToArray
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        return new FileReferenceToArray($value, new FileRepository(), $resourceFactory, $typolinkConverter);
    }
}
