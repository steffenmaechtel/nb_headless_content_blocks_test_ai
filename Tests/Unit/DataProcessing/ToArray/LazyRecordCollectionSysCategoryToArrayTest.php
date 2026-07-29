<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Domain\RecordInterface;

final class LazyRecordCollectionSysCategoryToArrayTest extends TestCase
{
    public function testCategoriesAreReducedToSystemFields(): void
    {
        $category = $this->createMock(RecordInterface::class);
        $category->method('toArray')->willReturn([
            'uid' => 1,
            'pid' => 2,
            'title' => 'Category',
            'slug' => 'category',
            'description' => 'Not exposed',
        ]);

        $collection = new LazyRecordCollection('categories', static function () use ($category): array {
            return ['category' => $category];
        });

        $subject = new LazyRecordCollectionSysCategoryToArray($collection);

        self::assertSame([
            'category' => [
                'uid' => 1,
                'pid' => 2,
                'title' => 'Category',
            ],
        ], $subject->toArray());
    }
}
