<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyFolderCollectionToArray;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Resource\Collection\LazyFolderCollection;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ResourceStorage;

final class LazyFolderCollectionToArrayTest extends TestCase
{
    public function testFolderIdentifiersAreNormalized(): void
    {
        $storage = $this->createMock(ResourceStorage::class);
        $storage->method('getConfiguration')->willReturn(['basePath' => 'user_upload/']);

        $folderWithSlash = $this->createMock(Folder::class);
        $folderWithSlash->method('getStorage')->willReturn($storage);
        $folderWithSlash->method('getIdentifier')->willReturn('/images');

        $folderWithoutSlash = $this->createMock(Folder::class);
        $folderWithoutSlash->method('getStorage')->willReturn($storage);
        $folderWithoutSlash->method('getIdentifier')->willReturn('documents');

        $collection = new LazyFolderCollection('folders', static function () use ($folderWithSlash, $folderWithoutSlash): array {
            return [
                'first' => $folderWithSlash,
                'second' => $folderWithoutSlash,
            ];
        });

        $subject = new LazyFolderCollectionToArray($collection);

        self::assertSame([
            'first' => '/user_upload/images',
            'second' => '/user_upload/documents',
        ], $subject->toArray());
    }
}
