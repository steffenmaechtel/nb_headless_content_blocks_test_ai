<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Generator;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\LazyRecordCollectionSysCategoryToArray;
use TYPO3\CMS\Core\Collection\LazyRecordCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class LazyRecordCollectionSysCategoryToArrayTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLazyRecordCollectionSysCategoryToArrayReturnsBasicData(): void
    {
        $record1 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record1->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 1, 'pid' => 0, 'title' => 'Category A']);

        $record2 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record2->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 2, 'pid' => 0, 'title' => 'Category B']);

        $lazyCollection = $this->createLazyRecordCollection([
            'category1' => $record1,
            'category2' => $record2,
        ]);

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('category1', $result);
        self::assertArrayHasKey('category2', $result);
    }

    public function testLazyRecordCollectionSysCategoryToArrayWithOneCategory(): void
    {
        $record = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 123, 'pid' => 456, 'title' => 'Single Category']);

        $lazyCollection = $this->createLazyRecordCollection([
            'singleCategory' => $record,
        ]);

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertArrayHasKey('singleCategory', $result);
        self::assertArrayHasKey('uid', $result['singleCategory']);
        self::assertArrayHasKey('pid', $result['singleCategory']);
        self::assertArrayHasKey('title', $result['singleCategory']);
        self::assertEquals(123, $result['singleCategory']['uid']);
        self::assertEquals(456, $result['singleCategory']['pid']);
        self::assertEquals('Single Category', $result['singleCategory']['title']);
    }

    public function testLazyRecordCollectionSysCategoryToArrayWithEmptyCollection(): void
    {
        $lazyCollection = $this->createLazyRecordCollection([]);

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testLazyRecordCollectionSysCategoryToArrayEmptyGenerator(): void
    {
        $lazyCollection = $this->createLazyRecordCollection((function (): Generator {
            yield from [];
        })());

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertEmpty($result);
    }

    public function testLazyRecordCollectionSysCategoryToArrayWithMultipleCategories(): void
    {
        $record1 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record1->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 1, 'pid' => 0, 'title' => 'Category 1']);

        $record2 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record2->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 2, 'pid' => 0, 'title' => 'Category 2']);

        $record3 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record3->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 3, 'pid' => 0, 'title' => 'Category 3']);

        $lazyCollection = $this->createLazyRecordCollection([
            'cat1' => $record1,
            'cat2' => $record2,
            'cat3' => $record3,
        ]);

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(3, $result);
        self::assertArrayHasKey('cat1', $result);
        self::assertArrayHasKey('cat2', $result);
        self::assertArrayHasKey('cat3', $result);
    }

    public function testLazyRecordCollectionSysCategoryToArrayWithDifferentPids(): void
    {
        $record1 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record1->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 1, 'pid' => 0, 'title' => 'Category 1']);

        $record2 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record2->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 2, 'pid' => 10, 'title' => 'Category 2']);

        $lazyCollection = $this->createLazyRecordCollection([
            'cat1' => $record1,
            'cat2' => $record2,
        ]);

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('cat1', $result);
        self::assertArrayHasKey('cat2', $result);
        self::assertEquals(0, $result['cat1']['pid']);
        self::assertEquals(10, $result['cat2']['pid']);
    }

    public function testLazyRecordCollectionSysCategoryToArrayWithComplexTitles(): void
    {
        $record = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record->expects(self::once())
            ->method('toArray')
            ->willReturn([
                'uid' => 999,
                'pid' => 100,
                'title' => 'Complex Title With <span>HTML</span> & "quotes"\\\'',
            ]);

        $lazyCollection = $this->createLazyRecordCollection([
            'complex' => $record,
        ]);

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('complex', $result);
        self::assertEquals(999, $result['complex']['uid']);
        self::assertEquals(100, $result['complex']['pid']);
        self::assertEquals('Complex Title With <span>HTML</span> & "quotes"\\\'', $result['complex']['title']);
    }

    public function testLazyRecordCollectionSysCategoryToArrayWithNumbers(): void
    {
        $record = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 1, 'pid' => 0, 'title' => '']);

        $lazyCollection = $this->createLazyRecordCollection([
            'numbered' => $record,
        ]);

        $subject = new LazyRecordCollectionSysCategoryToArray($lazyCollection);
        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertArrayHasKey('numbered', $result);
        self::assertEquals('');

        $record2 = $this->createMock(\TYPO3\CMS\Core\Domain\Record::class);
        $record2->expects(self::once())
            ->method('toArray')
            ->willReturn(['uid' => 2, 'pid' => 0, 'title' => 123]);

        $lazyCollection2 = $this->createLazyRecordCollection([
            'numeric' => $record2,
        ]);

        $subject2 = new LazyRecordCollectionSysCategoryToArray($lazyCollection2);
        $result2 = $subject2->toArray();

        self::assertIsArray($result2);
        self::assertArrayHasKey('numeric', $result2);
        self::assertEquals(123, $result2['numeric']['title']);
    }

    private function createLazyRecordCollection(array $items = []): LazyRecordCollection
    {
        return new class ($items) extends LazyRecordCollection {
            public function __construct(private readonly array $items) {}

            public function getIterator(): Generator
            {
                foreach ($this->items as $key => $record) {
                    yield $key => $record;
                }
            }
        };
    }
}