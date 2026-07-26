<?php

declare(strict_types=1);

namespace Netzbewegung\NbHeadlessContentBlocks\Tests\Unit\DataProcessing\ToArray;

use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\RecordToArray;
use Netzbewegung\NbHeadlessContentBlocks\DataProcessing\ToArray\TypolinkParameterToArray;
use TYPO3\CMS\Core\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class RecordToArrayTest extends UnitTestCase
{
    public function testConvertsRecordToArray(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: 'https://example.com'));

        $subject = new RecordToArray(
            ['uid' => 1, 'pid' => 2, 'colPos' => 3, 'CType' => 'test', 'my_field' => 'value'],
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayHasKey('my_field', $result);
        self::assertSame('value', $result['my_field']);
        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayNotHasKey('colPos', $result);
        self::assertArrayNotHasKey('CType', $result);
    }

    public function testRemovesSystemFields(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new RecordToArray(
            [
                'uid' => 1,
                'pid' => 2,
                't3ver_oid' => 3,
                't3ver_wsid' => 4,
                't3ver_state' => 5,
                't3ver_stage' => 6,
                't3ver_count' => 7,
                't3ver_tstamp' => 8,
                't3ver_move_id' => 9,
                'sys_language_uid' => 10,
                'l10n_parent' => 11,
                'l10n_diffsource' => 12,
                'my_field' => 'value',
            ],
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayNotHasKey('uid', $result);
        self::assertArrayNotHasKey('pid', $result);
        self::assertArrayNotHasKey('t3ver_oid', $result);
        self::assertArrayNotHasKey('t3ver_wsid', $result);
        self::assertArrayNotHasKey('t3ver_state', $result);
        self::assertArrayNotHasKey('t3ver_stage', $result);
        self::assertArrayNotHasKey('t3ver_count', $result);
        self::assertArrayNotHasKey('t3ver_tstamp', $result);
        self::assertArrayNotHasKey('t3ver_move_id', $result);
        self::assertArrayNotHasKey('sys_language_uid', $result);
        self::assertArrayNotHasKey('l10n_parent', $result);
        self::assertArrayNotHasKey('l10n_diffsource', $result);
        self::assertArrayHasKey('my_field', $result);
    }

    public function testRemovesTypolinkField(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: 'https://example.com'));

        $subject = new RecordToArray(
            [
                'my_link' => new TypolinkParameter(url: 'https://example.com'),
                'my_field' => 'value',
            ],
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertArrayNotHasKey('my_link', $result);
        self::assertArrayHasKey('my_field', $result);
    }

    public function testHandlesFileDoesNotExistException(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new RecordToArray(
            ['my_field' => 'value'],
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertSame(['my_field' => 'value'], $result);
    }

    public function testDelegatesToTypolinkParameterToArray(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: 'https://example.com'));

        $subject = new RecordToArray(
            ['my_field' => 'value'],
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertSame(['my_field' => 'value'], $result);
    }

    public function testReturnsEmptyArrayForEmptyRecord(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new RecordToArray(
            [],
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertSame([], $result);
    }

    public function testReturnsEmptyArrayForNullRecord(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new RecordToArray(
            null,
            $resourceFactory,
            $typolinkConverter
        );

        $result = $subject->toArray();

        self::assertIsArray($result);
        self::assertCount(0, $result);
    }

    public function testReturnsEmptyArrayForNonArrayRecord(): void
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        $subject = new RecordToArray(
            'not_an_array',
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
    private function createSubject($value, array $typolinkParameters = []): RecordToArray
    {
        $resourceFactory = new ResourceFactory();
        $typolinkConverter = new TypolinkParameterToArray(new TypolinkParameter(url: ''));

        return new RecordToArray($value, $resourceFactory, $typolinkConverter);
    }
}
